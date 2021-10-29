import urllib.request
import json
import datetime
from os import getenv

class db_error(Exception):
    pass

class user_type_error(Exception):
    pass

class push_error():
    def send_error (error, user_id):
        """
        エラーが発生したIDと内容を自分のLineへ送る（一応実装）
        """
        #https://note.nkmk.me/python-datetime-now-today/
        now_time = datetime.datetime.now()
        #https://qiita.com/danishi/items/07dd1b2f2a28255f7a85

        token = getenv('LINE_CHANNEL_ACCESS_TOKEN', None)

        req_header = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token ,
            
        }

        data = json.dumps({
        "to": user_id,
        "messages":[
            {
                "type":"text",
                "text":" 送信エラー \n  " + str(error) + "\n" + now_time.strftime('%Y年%m月%d日 %H:%M:%S')
            }
            ]
    })
        errorpush = urllib.request.Request('https://api.line.me/v2/bot/message/push', data=data.encode(), method='POST', headers=req_header)
        with urllib.request.urlopen(errorpush) as response:
            body = json.loads(response.read())
            headers = response.getheaders()
            status = response.getcode()

            print(headers)
            print(status)
            #print(body)