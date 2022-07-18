#-*- coding: utf-8 -*-
from os import getenv
import cx_Oracle
import urllib.request
import json
import datetime
from linebot_class import push_error

token = getenv('LINE_CHANNEL_ACCESS_TOKEN', None)

my_user_id = 'U90f4ffb3d9b7901673ff86738a3067ea'

day = datetime.date.today()

hour = int(datetime.datetime.now().strftime('%H'))

print(day, hour)

print("get_line_name")

def get_connection():
    """
    DBへの接続
    """
    try:
        conn = cx_Oracle.connect(user="admin", password= getenv('pass',0), dsn="db202110141010_high")
        return conn
    except cx_Oracle.DatabaseError as e:
            push_error.send_error(e, my_user_id)

def get_line_name():
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("SELECT on_duty FROM mainid WHERE on_duty != 'NOT' AND on_duty IS NOT NULL")
                for col in cur:

                    url = 'https://api.line.me/v2/bot/profile/' + col[0]
                    req_header = {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token ,
                    }

                    push = urllib.request.Request(url, method='GET', headers=req_header)

                    with urllib.request.urlopen(push) as response:
                        body = json.loads(response.read())
                        status = response.getcode()

                        print(status)
                
                    with get_connection() as conn:
                        with conn.cursor() as cur:
                            cur.execute("MERGE INTO duty A USING (SELECT :0 AS user_id, :1 AS user_name FROM dual) b ON (a.user_id = b.user_id) WHEN MATCHED THEN UPDATE SET user_name = :1 WHEN NOT MATCHED THEN INSERT (user_id, user_name) VALUES (:0, :1 )", (col[0], body["displayName"]),)
                            conn.commit()

    except urllib.error.URLError as e:
        print(e.reason)
        push_error.send_error(e, my_user_id)

def DELETE(DELETE, id):
    """
    引数を元にdbをDELETEする
    """
    #https://qiita.com/hoto17296/items/0ca1569d6fa54c7c4732
    try:
        with mainpostgresql.db_connect() as conn:
            with conn.cursor() as cur:
                cur.execute("DELETE FROM mainid WHERE status = 6 and LASTPUSH <= ADD_MONTHS(:0, -5)", (day,))
            conn.commit()

    except cx_Oracle.DatabaseError:
        print(e.reason)
        push_error.send_error(e, my_user_id)

get_line_name()
