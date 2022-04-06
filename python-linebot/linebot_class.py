import cx_Oracle
from os import getenv
from error_class import push_error, db_error, user_type_error

class mainpostgresql():
    my_user_id = 'U90f4ffb3d9b7901673ff86738a3067ea'

    def db_connect():
        """
        データベースと接続する
        """
        try:

            conn = cx_Oracle.connect(user="admin", password= getenv('pass',0), dsn="db202110141010_high")
            return conn
        except cx_Oracle.DatabaseError as e:
            push_error.send_error(e, mainpostgresql.my_user_id)
            raise cx_Oracle.DatabaseError

    def make_table_main():
        """
        テーブルを作成(仮登録テーブル)
        (デバッグ用)
        """
        #https://www.curict.com/item/20/20f70b7.html
        with mainpostgresql.db_connect().cursor() as conn:
            cur = conn.cursor()
            cur.execute("""
                    create table mainid (
                    id varchar2(32),
                    room_name varchar2(128),
                    on_duty varchar2(128),
                    user_id varchar2(35) NOT NULL,
                    time int,
                    status int,
                    datetime date,
                    frequency int,
                    lastpush date,
                    PRIMARY KEY (id)
                    )
                    """)
            mainpostgresql.db_connect().commit()

    def update(column, update, user_id, commit, mode, id=None, etccolum=None, etcdata=None):
        """
        columnへupdateの内容をDBへupdateする、ユーザーIDはuser_idに入れる
        modeを2にするとetccolumに入ったカラムを元にupdateする
        modeを7にすると主キーをidから取得したものを利用する
        commitにTrueを入れるとコミットも行う(連続処理時はパフォーマンスを考慮してFalseにする)
        """
        #https://qiita.com/hoto17296/items/0ca1569d6fa54c7c4732
        try:
            with mainpostgresql.db_connect() as conn:
                with conn.cursor() as cur:
                    if mode:
                        cur.execute(f"UPDATE mainid SET {column} = :1 WHERE user_id in ( :2 ) and {etccolum} = :3 ", (update, user_id, etcdata))

                    else:
                        cur.execute(f"UPDATE mainid SET {column} = :1 WHERE user_id in ( :2 ) and id in ( :3 )", (update, user_id, id))

                if commit:
                    pass

                conn.commit()
                
        except cx_Oracle.Error as e:
            print(e)
            raise user_type_error

        except cx_Oracle.DatabaseError as e:
            print(e)
            raise db_error

    def insert_1st(insert):
            """
            引数の内容をカラムへINSERTする(本登録テーブル)
            初回登録時に使用
            """
            #https://qiita.com/hoto17296/items/0ca1569d6fa54c7c4732
            try:
                #ユーザーIDが未登録時の処理
                    with mainpostgresql.db_connect() as conn:
                        with conn.cursor() as cur:
                            sql = "INSERT INTO mainid (id, user_id, status) VALUES ( (SELECT SYS_GUID() FROM dual), :0, 1)"
                            cur.execute(sql, (insert,))
                            conn.commit()
                    
            except cx_Oracle.DatabaseError as e:
                raise db_error

    def status_cheking(user_id, column, status):
        """
        テーブルへuser_idのユーザーIDがcolumnのカラム名へstatusの内容が入っているか確認する
        """
        try:
            #参考元 https://compute-cucco.hatenablog.com/entry/2017/05/03/170038
            #https://www.atmarkit.co.jp/ait/articles/1201/13/news140.html
            with mainpostgresql.db_connect() as conn:
                with conn.cursor() as cur:
                    sql = f"SELECT CASE WHEN MAX(user_id) IS NULL THEN 'False' ELSE 'True' END User_exists FROM mainid WHERE user_id = :1 and {column} = :2"
                    cur.execute(sql, (user_id, status))
                    resolt = cur.fetchall()
                    return resolt
        except cx_Oracle.DatabaseError:
            raise db_error

    def get_rooms(user_id, msg="変更する部屋名を選択してください", tapmsg="これを変更する", msg_type=False, sendmsg=None):
        """
        user_idのを元にroom_nameを取得する
        msgはユーザーに表示する文章（上）tapmsgはユーザーに表示する文章（下）
        msg_typeはユーザーが下部分がタップできるか出来ないのかの指定
        Trueにした場合タップ時に主キーがメッセージアクションで送信されるようになる
        返り値はFlexSendMessageクラスで使えるdict型
        """
        try:
        #初期値 ここにforで値を足して送る
            req_data = ({
                        "type": "carousel",
                        "contents": [
                            
                        ]
                        })
            if msg_type:
                with mainpostgresql.db_connect() as conn:
                    with conn.cursor() as cur:
                        cur.execute("SELECT room_name, time FROM mainid WHERE user_id = :0", (user_id,))
                        for col in cur:
                            #タプルで帰ってくる
                            req_data["contents"] += [
                                                        {
                                                            "type": "bubble",
                                                            "direction": "ltr",
                                                            "header": {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                                {
                                                                "type": "text",
                                                                "text": f"{msg}",
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
                                                                "text": f"{col[0]}",
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
                                                                "type": "text",
                                                                "text": "通知時刻" f"{col[1]}" "時",
                                                                "align": "center",
                                                                "contents": []
                                                                }
                                                            ]
                                                            }
                                                        }
                                                        ]
            else:
                with mainpostgresql.db_connect() as conn:
                    with conn.cursor() as cur:
                        cur.execute("SELECT room_name, id FROM mainid WHERE user_id = :0", (user_id,))
                        for col in cur:
                            req_data["contents"] += [
                                                        {
                                                            "type": "bubble",
                                                            "direction": "ltr",
                                                            "header": {
                                                            "type": "box",
                                                            "layout": "baseline",
                                                            "contents": [
                                                                {
                                                                "type": "text",
                                                                "text": f"{msg}",
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
                                                                "text": f"{col[0]}",
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
                                                                    "type": "message",
                                                                    "label": f"{tapmsg}",
                                                                    "text": f"{col[1]}" + "," + sendmsg
                                                                }
                                                                }
                                                            ]
                                                            }
                                                        }
                                                        ]
            return req_data

        except  cx_Oracle.DatabaseError as e:
                #DB接続エラー時にエラー内容とともに自分のLineへ通知が行く
                push_error.send_error(e, mainpostgresql.my_user_id)

    def DELETE(DELETE, id):
        """
        引数を元にdbをDELETEする
        """
        #https://qiita.com/hoto17296/items/0ca1569d6fa54c7c4732
        try:
            with mainpostgresql.db_connect() as conn:
                with conn.cursor() as cur:
                    cur.execute("DELETE FROM mainid WHERE user_id = :0 and id = :1", (DELETE, id,))
                conn.commit()

        except cx_Oracle.DatabaseError:
            raise db_error

class duty:
    def make_table_user_name():
        """
        テーブルを作成(仮登録テーブル)
        (デバッグ用)
        """
        #https://www.curict.com/item/20/20f70b7.html
        with mainpostgresql.db_connect() as conn:
            cur = conn.cursor()
            cur.execute("""
                    create table duty (
                    id NUMBER GENERATED ALWAYS AS IDENTITY,
                    user_id CHAR(35) NOT NULL,
                    user_name CHAR(128) NOT NULL
                    )
                    """)
            mainpostgresql.db_connect().commit()
