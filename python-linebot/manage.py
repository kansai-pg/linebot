# -*- coding: utf-8 -*-

#  Licensed under the Apache License, Version 2.0 (the "License"); you may
#  not use this file except in compliance with the License. You may obtain
#  a copy of the License at
#
#       http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
#  WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
#  License for the specific language governing permissions and limitations
#  under the License.

import datetime
import os
import sys
import json
import unicodedata
import ast

from flask import Flask, request, abort
from linebot_class import mainpostgresql
from error_class import db_error, user_type_error

from linebot import (
    LineBotApi, WebhookHandler
)
from linebot.exceptions import (
    LineBotApiError, InvalidSignatureError
)
from linebot.models import (
    MessageEvent, TextMessage, TextSendMessage,
    SourceGroup, SourceRoom, SourceUser,
    MessageAction, QuickReplyButton, QuickReply, JoinEvent, FlexSendMessage)

app = Flask(__name__)
#app.wsgi_app = ProxyFix(app.wsgi_app, x_for=1, x_host=1, x_proto=1)

# get channel_secret and channel_access_token from your environment variable
channel_secret = os.getenv('LINE_CHANNEL_SECRET', None)
channel_access_token = os.getenv('LINE_CHANNEL_ACCESS_TOKEN', None)
if channel_secret is None or channel_access_token is None:
    print('Specify LINE_CHANNEL_SECRET and LINE_CHANNEL_ACCESS_TOKEN as environment variables.')
    sys.exit(1)

line_bot_api = LineBotApi(channel_access_token)
handler = WebhookHandler(channel_secret)

notroom = "部屋名が未登録です。先に「登録」コマンドで部屋名を登録してください"
# JSON文字列をdict型に変換
flex_json_dict = json.load(open('time_list.json', 'r'))
flex_json_dict_rooms = json.load(open('roomoptions.json', 'r'))
flex_json_dict_msg = json.load(open('msg.json', 'r'))

@app.route("/callback", methods=['POST'])
def callback():
    # get X-Line-Signature header value
    signature = request.headers['X-Line-Signature']

    # get request body as text
    body = request.get_data(as_text=True)
    app.logger.info("Request body: " + body)

    # handle webhook body
    try:
        handler.handle(body, signature)
    except LineBotApiError as e:
        print("Got exception from LINE Messaging API: %s\n" % e.message)
        for m in e.error.details:
            print("  %s: %s" % (m.property, m.message))
        print("\n")
    except InvalidSignatureError:
        abort(400)

    return 'OK'

@handler.add(MessageEvent, message=TextMessage)
def handle_text_message(event):

    try:
        #http://eneprog.blogspot.com/2018/09/pythonunicodedata.html
        #半角全角両方対応できるようにする
        text = unicodedata.normalize("NFKC", event.message.text)
        text.strip()

        if text == 'help':
            line_bot_api.reply_message(
                event.reply_token, TextSendMessage(text="botの利用規約はこちらをご参照ください\n https://linebot-kansai-web.herokuapp.com \n 登録は「登録」コマンドで可能です。\n 日時設定はdatesetで可能です。"))

        def user_update(user_id, send_on_user=False):
            """
            ユーザーが登録と入力した際の処理
            """
            #入力待機中に再度updateを入力したかを判定する。
            #これが無いと同じデータが複数、テーブルへ保存される
            if ast.literal_eval(mainpostgresql.status_cheking(user_id, "status", 1)[0][0]):
                #テーブルにステータスIDが含まれているとすでに待機中であると送信する。
                line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text="すでに待機状態です。このまま入力してください"))
            else:
                #テーブルにIDが含まれていない場合、IDを登録する
                    mainpostgresql.insert_1st(user_id)
            if send_on_user:
                mainpostgresql.update("on_duty", "NOT", user_id, True, True, etccolum="status", etcdata=1)

            line_bot_api.reply_message(event.reply_token, FlexSendMessage(alt_text='部屋名登録', contents=flex_json_dict_rooms))

        def after_update(user_id, user_text, send_on_user=False):
                """
                入力受付の処理
                """
                #ユーザーが「登録」コマンドを入力したか（本登録テーブルへステータスコードが[1]の状態で存在するか）判定
                #要素は1つしか無いがなぜかタプルで帰ってくるので[0]でstrへする
                if ast.literal_eval(mainpostgresql.status_cheking(user_id, "status", 1)[0][0]):
                    #ユーザーが送信した内容をDBへ書き込む
                    mainpostgresql.update("room_name", user_text, user_id, False, True, etccolum="status", etcdata=1)
                    #時間選択まで進んだというフラグを立てる
                    mainpostgresql.update("status", 2, user_id, True, True, etccolum="status", etcdata=1)
                    #https://www.line-community.me/ja/question/5d405945851f743fd7cd97c6/line-bot-designer-で作成した-flex-message-を-linebot-で送信したい
                    line_bot_api.reply_message(event.reply_token, FlexSendMessage(alt_text='通知時刻選択', contents=flex_json_dict))

                #ルーム設定まで進んだか
                elif ast.literal_eval(mainpostgresql.status_cheking(user_id, "status", 2)[0][0]):
                    #ユーザーが時刻設定で24時よりも大きい数字を送信していないかの確認
                    if int(user_text) <= 24:
                        #DBへ時刻情報をUPDATE
                        mainpostgresql.update("time", int(user_text), user_id, False, True, etccolum="status", etcdata=2)
                        #Python側で一週間後のdatetimeを生成してDBへUPDATE(heroku DBのタイムゾーンはUTCから変更不可)
                        mainpostgresql.update("datetime", datetime.date.today() + datetime.timedelta(days=7), user_id, False, True, etccolum="status", etcdata=2)
                        #何日ごとに通知するかをUPDATE
                        mainpostgresql.update("frequency", 7, user_id, False, True, etccolum="status", etcdata=2)
                        if not send_on_user:
                            #ステータスコードを当番入力待機に設定する
                            mainpostgresql.update("status", 3, user_id, True, True, etccolum="status", etcdata=2)
                            line_bot_api.reply_message(event.reply_token, FlexSendMessage(alt_text='当番設定', contents=flex_json_dict_msg))

                        else:
                            #ステータスコードを待機無しにする
                            mainpostgresql.update("status", 0, user_id, True, True, etccolum="status", etcdata=2)
                            #登録完了メッセージを送信
                            line_bot_api.reply_message(
                            event.reply_token, TextSendMessage(text="登録完了しました\n 通知頻度は「日時設定」コマンドで指定できます。\n 初期設定では7日後へ設定されています。"))
                    else:
                        raise user_type_error

                elif ast.literal_eval(mainpostgresql.status_cheking(user_id, "status", 4)[0][0]):
                    #ユーザーからの入力をUPDATE(int型でない場合はpsycopg2の例外が出てユーザーへ不正な値と送信する)
                    mainpostgresql.update("frequency", unicodedata.normalize("NFKC", user_text), user_id, False, True, etccolum="status", etcdata=4)
                    #https://qiita.com/hayasisiki/items/0adf43e1b91487654a7b
                    #Python側で入力数値を元にdatetimeを生成(もし7だったら7日後のdatetimeを生成)しUPDATE
                    mainpostgresql.update("datetime", datetime.date.today() + datetime.timedelta(days=int(user_text)), user_id, False, True, etccolum="status", etcdata=4)
                    #ステータスコードを待機なしへ変更
                    mainpostgresql.update("status", 0, user_id, True, True, etccolum="status", etcdata=4)
                    line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text="登録完了しました。"))

                elif ast.literal_eval(mainpostgresql.status_cheking(user_id, "status", 5)[0][0]):
                    if int(user_text) <= 24:
                        #DBへ時刻情報をinsert
                        mainpostgresql.update("time", int(user_text), user_id, False, True, etccolum="status", etcdata=5)
                        #入力待機する必要が無くなったのでDBのステータスコードを更新
                        mainpostgresql.update("status", 0, user_id, True, True, etccolum="status", etcdata=5)
                        line_bot_api.reply_message(event.reply_token, TextSendMessage(text="完了しました"))
                    else:
                        raise user_type_error

                else:
                    #1:1チャット以外には送信しないようにする
                    if isinstance(event.source, SourceUser):
                        #「登録」などを入力せずにいきなりメッセージを送信してきた場合の処理
                        line_bot_api.reply_message(
                        event.reply_token, TextSendMessage(text="登録と入力することで登録できます。"))

        def send_room_deta(user_id):
            """
            ユーザーへ通知頻度変更に関するメッセージを送信
            """
            #DBへuser_idが存在するかで部屋名が登録されているかを判定
            if ast.literal_eval(mainpostgresql.status_cheking(user_id, "user_id", user_id)[0][0]):
                #クラス側でjsonを生成し送信する
                line_bot_api.reply_message(event.reply_token, 
                FlexSendMessage(alt_text='通知設定変更', contents=mainpostgresql.get_rooms(user_id, sendmsg="DETA")))
            else:
                line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text=notroom))

        def send_room_deta_delete(user_id):
            """
            ユーザーへ削除に関するメッセージを送信
            """
            if ast.literal_eval(mainpostgresql.status_cheking(user_id, "user_id", user_id)[0][0]):
                line_bot_api.reply_message(event.reply_token, 
                FlexSendMessage(alt_text='削除部屋選択', contents=mainpostgresql.get_rooms(user_id, "削除する部屋名を選択してください", "これを削除", False, "DEL" )))
            else:
                line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text=notroom))

        def send_room_time(user_id):
            """
            ユーザーへ通知時刻変更に関するメッセージを送信
            """
            if ast.literal_eval(mainpostgresql.status_cheking(user_id, "user_id", user_id)[0][0]):
                line_bot_api.reply_message(event.reply_token, 
                FlexSendMessage(alt_text='時刻変更部屋選択', contents=mainpostgresql.get_rooms(user_id, sendmsg="TIME")))
            else:
                line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text=notroom))

        def roomsview(user_id):
            """
            登録されている部屋名などの情報を送信する
            """
            if ast.literal_eval(mainpostgresql.status_cheking(user_id, "user_id", user_id)[0][0]):
                line_bot_api.reply_message(event.reply_token, 
                    FlexSendMessage(alt_text='登録部屋表示', contents=mainpostgresql.get_rooms(user_id, "登録されている部屋の一覧です", None, msg_type=True)))
            else:
                line_bot_api.reply_message(
                    event.reply_token, TextSendMessage(text=notroom))

        def yes(user_id, user_text):
            """
            通知時に「掃除完了」を選択した際の処理
            """
            #dbへ通知時刻をupdate
            mainpostgresql.update("lastpush", datetime.date.today(), user_id, False, False, user_text[1])
            #通知した部屋名のステータスコードをupdate
            mainpostgresql.update("status", 0, user_id, True, False, user_text[1])
            line_bot_api.reply_message(event.reply_token, TextSendMessage(text="お疲れ様です\n 次も頑張りましょう！"))
        
        def time_set(user_id, user_text):
            """
            「時刻設定」を入力した際の処理
            """
            mainpostgresql.update("status", 5, user_id, True, False, user_text[0])
            line_bot_api.reply_message(event.reply_token, FlexSendMessage(alt_text='通知時刻選択', contents=flex_json_dict))

        def set_deta(user_id, user_text):
            """
            「日時設定」を入力した際の処理
            """
            mainpostgresql.update("status", 4, user_id, True, False, user_text[0])
            line_bot_api.reply_message(event.reply_token, 
                        TextSendMessage(text="何日ごとに通知するかを数値のみで入力してください（例: 一週間ごとの場合 7）\n また初回通知は入力日に行われます。（設定日が6月1日の場合6月7日から通知される)"))

        def gnot(user_id):
            """
            グループでの登録時に「当番なし」を選択した際の処理
            """
            mainpostgresql.update("status", 0, user_id, True, True, etccolum="status", etcdata=3)
            line_bot_api.reply_message(event.reply_token, TextSendMessage(text="当番なしで登録完了しました。"))

        def gme(user_id):
            """
            グループでの登録時に「当番あり」を選択した際の処理
            """
            mainpostgresql.update("on_duty", event.source.user_id, user_id, True, True, etccolum="status", etcdata=3)
            mainpostgresql.update("status", 0, user_id, False, True, etccolum="status", etcdata=3)
            line_bot_api.reply_message(event.reply_token, TextSendMessage(text="当番ありで登録完了しました。"))

        def room_delete(user_id, user_text):
            """
            削除する部屋を選択した際の処理
            """
            #メインテーブルから削除
            mainpostgresql.DELETE(user_id, user_text[0])
            line_bot_api.reply_message(
            event.reply_token, TextSendMessage(text="完了しました。"))

        def nope(user_id, user_text):
            """
            通知時に「まだです」を選択した際の処理
            """
            mainpostgresql.update("status", 0, user_id, True, False, user_text[1])
            line_bot_api.reply_message(event.reply_token, TextSendMessage(text="次はがんばりましょう！"))
        
        if text == '登録':
            #グループから投稿されているかを判定
            if isinstance(event.source, SourceGroup):
                user_update(event.source.group_id)

            #トークルームから投稿されているかを判定
            elif isinstance(event.source, SourceRoom):
                user_update(event.source.room_id)
            
            #1:1チャットから投稿されているかを判定
            elif isinstance(event.source, SourceUser):
                user_update(event.source.user_id, True)

        elif text == '日時設定':

            if isinstance(event.source, SourceGroup):
                send_room_deta(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                send_room_deta(event.source.room_id)

            elif isinstance(event.source, SourceUser):
                send_room_deta(event.source.user_id)

        elif text == "削除":

            if isinstance(event.source, SourceGroup):
                send_room_deta_delete(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                send_room_deta_delete(event.source.room_id)

            elif isinstance(event.source, SourceUser):
                send_room_deta_delete(event.source.user_id)

        elif text == '時刻設定':

            if isinstance(event.source, SourceGroup):
                send_room_time(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                send_room_time(event.source.room_id)

            elif isinstance(event.source, SourceUser):
                send_room_time(event.source.user_id)

        elif text == '登録一覧':

            if isinstance(event.source, SourceGroup):
                roomsview(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                roomsview(event.source.room_id)
            
            elif isinstance(event.source, SourceUser):
                roomsview(event.source.user_id)

        #flexメッセージを使って送信された内容にYESが含まれているか
        elif 'YES' in text:

            if isinstance(event.source, SourceGroup):
                yes(event.source.group_id, text.split(','))

            elif isinstance(event.source, SourceRoom):
                yes(event.source.room_id, text.split(','))

            elif isinstance(event.source, SourceUser):
                yes(event.source.user_id, text.split(','))

        elif 'TIME' in text:
            if isinstance(event.source, SourceGroup):
                time_set(event.source.group_id, text.split(','))

            elif isinstance(event.source, SourceRoom):
                time_set(event.source.room_id, text.split(','))
            
            elif isinstance(event.source, SourceUser):
                time_set(event.source.user_id, text.split(','))

        elif 'DETA' in text:
            if isinstance(event.source, SourceGroup):
                set_deta(event.source.group_id, text.split(','))

            elif isinstance(event.source, SourceRoom):
                set_deta(event.source.room_id, text.split(','))

            elif isinstance(event.source, SourceUser):
                set_deta(event.source.user_id, text.split(','))

        elif 'DEL' in text:
            if isinstance(event.source, SourceGroup):
                room_delete(event.source.group_id, text.split(','))

            elif isinstance(event.source, SourceRoom):
                room_delete(event.source.room_id, text.split(','))
        
            elif isinstance(event.source, SourceUser):
                room_delete(event.source.user_id, text.split(','))

        elif 'nope' in text:
            if isinstance(event.source, SourceGroup):
                nope(event.source.group_id, text.split(','))

            elif isinstance(event.source, SourceRoom):
                nope(event.source.room_id, text.split(','))

            elif isinstance(event.source, SourceUser):
                nope(event.source.user_id, text.split(','))


        elif text == "not":
            if isinstance(event.source, SourceGroup):
                gnot(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                gnot(event.source.room_id)

        elif text == "me":
            if isinstance(event.source, SourceGroup):
                gme(event.source.group_id)

            elif isinstance(event.source, SourceRoom):
                gme(event.source.room_id)

        else:
            if isinstance(event.source, SourceGroup):
                #ここの部分は半角全角変換を行わない（ユーザーの入力内容が正しく扱われない可能性を避けるため）
                after_update(event.source.group_id, event.message.text)
        
            elif isinstance(event.source, SourceRoom):
                after_update(event.source.room_id, event.message.text)

            elif isinstance(event.source, SourceUser):
                after_update(event.source.user_id, event.message.text, True)

    except OverflowError:
        line_bot_api.reply_message(
        event.reply_token, TextSendMessage(text="入力された数値が大きすぎます"))

    except (user_type_error, ValueError):
        line_bot_api.reply_message(
        event.reply_token, TextSendMessage(text="不正な値です。正しい値を入力、もしくは選択してください"))

    except db_error:
        line_bot_api.reply_message(
        event.reply_token, TextSendMessage(text="データベース接続エラーです。\n すみませんが、しばらくしてから再度お試しください"))

    

#友達追加orグループなどに追加された際の処理
@handler.add(JoinEvent)
def handle_join(event):
    line_bot_api.reply_message(
        event.reply_token,
        TextSendMessage(text=event.source.type + 'への登録ありがとうございます。\n掃除通知をお知らせします。\nbotの利用規約はこちらをご参照ください\n https://line-bot-help.japan-is.fun \n 「登録」コマンドで登録出来ます。\n 時刻設定はdatesetで登録できます。\n その他の使い方はhelpと入力してください'))

if __name__ == '__main__':
    app.run()
