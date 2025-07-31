# BlitZlog
**BlitZlog**は、軽量でモジュラーな思考で目指しているPHPベースのCMSです。<br>
Beta版なので、動作が不安定の可能性があります。

## 動作要件
- PHPバージョン<br>
**動作可能**：7.0～<br>
**推奨**：7.4～
- モジュール関連<br>
以下になっていれば動作します。
```ini
; GitHubでのアップデートなど
extension=curl
; ファイルの情報
extension=fileinfo
; データベース
extension=sqlite3
; SSL接続
extension=openssl
; Zip 圧縮/展開 
extension=zip
```
- allow_url_fopen ON<br>
この設定をONにしてください。（インストーラーやアップデート等で使用するため）
