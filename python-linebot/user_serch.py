#-*- coding: utf-8 -*-
from os import getenv
import cx_Oracle
import urllib.request
import json
import datetime
from linebot_class import push_error
import sys

token = getenv('LINE_CHANNEL_ACCESS_TOKEN', None)

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
                cur.execute("select user_id, room_name, TO_CHAR(lastpush,'yyyy/mm/dd'), ID from mainid WHERE LASTPUSH <= ADD_MONTHS(:0, -3)", (day,))
                for col in cur:
                    #jsonで送信内容を書く
                    req_data = {
                        "to": col[0],
                        "messages": [
                            {
                            "type": "flex",
                            "altText": col[1] + "掃除に関する返答が長期間ありません。",
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
                                    "text": col[1] + "掃除に関する返答が長期間ありません。",
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
                                            "text": col[1] + "掃除に関する返答が三ヶ月以上ありません。\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "前回の掃除日は" + str(col[2]) + "です\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "※i一度も返答が無い場合は\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "登録日が表示されます\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "このまま返答がない場合は\n",
                                            "align": "center",
                                            "wrap": True,
                                            },
                                            {
                                            "type": "text",
                                            "text": "この名称への通知情報が自動削除されます\n",
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
                                    "label": "削除しない",
                                    "text": str(col[3]) + ",NODEL"
                                    }
                                },
                                {
                                    "type": "button",
                                    "action": {
                                    "type": "message",
                                    "label": "削除する",
                                    "text": str(col[3]) + ",DEL"
                                    }
                                }
                                ]
                            }
                            }
                            }
                        ]
                            }

                    #頻度のデータを更新する（frequencyに7日が入っていた場合実行日から7日語のdatatimeをinsertする
                    with get_connection() as conn:
                        with conn.cursor() as cur:
                            cur.execute("UPDATE mainid SET status = 6 WHERE id= :1", (col[3],))
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

    except cx_Oracle.DatabaseError as e:
        #DB接続エラーの時にエラー内容とともに自分のLineへ通知が行く
        push_error.send_error(e, my_user_id)

scheduled_job()
