import datetime

class datetime():
    def datatime():
        date_source = datetime.date.today()

        flex_json = {
            "type": "bubble",
            "size": "giga",
            "direction": "ltr",
            "header": {
            "type": "box",
            "layout": "vertical",
            "contents": [
                {
                "type": "text",
                "text": "日時選択",
                "align": "center",
                "contents": []
                }
            ]
            },
            "body": {
            "type": "box",
            "layout": "vertical",
            "contents": [
                {
                "type": "text",
                "text": "日時を選択してください",
                "align": "center",
                "contents": []
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
                    "type": "datetimepicker",
                    "label": "data",
                    "data": "data",
                    "mode": "date",
                    "initial": date_source,
                    "max": "2022-06-22",
                    "min": date_source
                }
                }
            ]
            }
        }
        
        return flex_json