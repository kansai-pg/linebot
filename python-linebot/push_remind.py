#-*- coding: utf-8 -*-
from os import getenv
import cx_Oracle
import urllib.request
import json
import datetime
from linebot_class import push_error
import sys

#https://note.nkmk.me/python-datetime-now-today/

token = getenv('LINE_CHANNEL_ACCESS_TOKEN', None)
#https://qiita.com/danishi/items/07dd1b2f2a28255f7a85

url = 'https://api.line.me/v2/bot/message/push'
req_header = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token ,
    
}

my_user_id = 'U90f4ffb3d9b7901673ff86738a3067ea'

def get_connection():
    try:
        conn = cx_Oracle.connect(user="admin", password= getenv('pass',0), dsn="db202110141010_high")
        return conn
    except cx_Oracle.DatabaseError as e:
        push_error.send_error(e, my_user_id)
        raise cx_Oracle.DatabaseError

def scheduled_job():
    day = datetime.date.today()
    hour = int(datetime.datetime.now().strftime('%H'))
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                print(day, hour)
                #実行時の時刻を取得し、通知する全ユーザーのIDを一旦配列に格納
                #https://rfs.jp/sb/sql/s03/03_2-2.html
                #改行
                #https://developers.line.biz/ja/docs/messaging-api/flex-message-elements/
                #https://www.sejuku.net/blog/56589
                #https://stellacreate.com/entry/oracle-sysdate#toc2
                cur.execute("SELECT mainid.user_id, room_name, frequency, TO_CHAR(lastpush,'yyyy/mm/dd'), mainid.id, user_name FROM mainid LEFT OUTER JOIN duty ON mainid.on_duty = duty.user_id WHERE datetime = :0 AND time <= :1", (day, hour,))
                for col in cur:
                    #jsonで送信内容を書く
                    req_data = {
                        "to": col[0],
                        "messages": [
                            {
                            "type": "flex",
                            "altText": col[1] + "掃除の時間です。",
                            "contents": {
                            "type": "bubble",
                            "size": "mega",
                            "direction": "ltr",
                            "header": {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                {
                                    "type": "text",
                                    "text": col[1] + "掃除の時間です",
                                    "align": "center",
                                }
                                ]
                            },
                            "body": {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                            {
                                            "type": "text",
                                            "text": col[1] + "掃除の時間です\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "前回の掃除日は" + str(col[3]) + "です\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "※初回通知の場合は\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "登録日が表示されます。\n",
                                            "align": "center",
                                            "wrap": True,
                                            }
                                ]
                            },
                            "footer": {
                                "type": "box",
                                "layout": "horizontal",
                                "contents": [
                                {
                                    "type": "button",
                                    "action": {
                                    "type": "message",
                                    "label": "掃除完了！",
                                    "text": "YES," + str(col[4])
                                    }
                                },
                                {
                                    "type": "button",
                                    "action": {
                                    "type": "message",
                                    "label": "まだです",
                                    "text": "nope," + str(col[4])
                                    }
                                }
                                ]
                            }
                            }
                            }
                        ]
                            }
                    if col[5]:
                        req_data["messages"][0]['contents']["body"] = {
                                        "type": "box",
                                        "layout": "vertical",
                                        "contents": [
                                            {
                                            "type": "text",
                                            "text": col[1] + "掃除の時間です\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "前回の掃除日は" + str(col[3]) + "です\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "※初回通知の場合は\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "Noneと表示されます\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "当番は\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": col[5] + "です。",
                                            "align": "center",
                                            "wrap": True,
                                            }
                                        ]
                                        }

                    #頻度のデータを更新する（frequencyに7日が入っていた場合実行日から7日語のdatatimeをinsertする
                    with get_connection() as conn:
                        with conn.cursor() as cur:
                            cur.execute("UPDATE mainid SET status = 7, datetime= :0 WHERE id= :1", (datetime.date.today() + datetime.timedelta(days=col[2]), col[4]))
                            conn.commit()

                    #push通知送信処理
                    push = urllib.request.Request(url, data=json.dumps(req_data).encode(), method='POST', headers=req_header)

                    try:
                        with urllib.request.urlopen(push) as response:
                            body = json.loads(response.read())
                            headers = response.getheaders()
                            status = response.getcode()

                            print(headers)
                            print(status)

                    except urllib.error.URLError as e:
                        print(e.reason)
                        #送信エラー時に自分のLineへ通知が行く、Line側の障害だったら当然詰み
                        push_error.send_error(e.reason, my_user_id)

        #ループ処理を抜けた後にコミットして通知頻度をupdateする
        #conn.commit()
    except cx_Oracle.DatabaseError as e:
        #DB接続エラーの時にエラー内容とともに自分のLineへ通知が行く
        push_error.send_error(e, my_user_id)

scheduled_job()
