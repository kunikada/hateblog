<?php echo doctype('html5') ?>
<html lang="ja">
<?php echo $header ?>
<body>
<?php echo $content_header ?>
<div class="white-paper">
    <div class="container">
        <div class="content">
            <form action="/user/login" method="post" class="form-content">
<?php if ($lastError == 'failpass'): ?>
                <div class="control-group error">
                    <label class="control-label" for="loginUsername">ユーザー名</label>
                    <input type="text" id="loginUsername" name="username" value="<?php echo $username ?>" />
                </div>
                <div class="control-group error">
                    <label class="control-label" for="loginPassword">パスワード</label>
                    <input type="password" id="loginPassword" name="password" value="<?php echo $password ?>" />
                    <span class="help-block">ユーザー名またはパスワードが違います。</span>
                </div>
<?php else: ?>
                <div class="control-group">
                    <label class="control-label" for="loginUsername">ユーザー名</label>
                    <input type="text" id="loginUsername" name="username" />
                </div>
                <div class="control-group">
                    <label class="control-label" for="loginPassword">パスワード</label>
                    <input type="password" id="loginPassword" name="password" />
                </div>
<?php endif ?>
                <label class="checkbox">
                    <input type="checkbox" name="autologin" value="on" />
                    ログイン状態を保存する
                </label>
                <button type="submit" class="btn btn-info">ログイン</button>
            </form>
        </div>
        <div class="content">
            <form action="/user/regist" method="post" class="form-content" autocomplete="off">
                <legend class="title-legend">アカウント登録</legend>
<?php if ($lastError == 'failuser'): ?>
                <div class="control-group error">
                    <label class="control-label" for="entryUsername">ユーザー名</label>
                    <input type="text" id="entryUsername" name="username" value="<?php echo $username ?>" />
                    <span class="help-block">すでに登録されています。</span>
                </div>
                <div class="control-group">
                    <label class="control-label" for="entryPassword">パスワード</label>
                    <input type="password" id="entryPassword" name="password" value="<?php echo $password ?>" />
                </div>
<?php else: ?>
                <div class="control-group">
                    <label class="control-label" for="entryUsername">ユーザー名</label>
                    <input type="text" id="entryUsername" name="username" />
                </div>
                <div class="control-group">
                    <label class="control-label" for="entryPassword">パスワード</label>
                    <input type="password" id="entryPassword" name="password" />
                </div>
<?php endif ?>
                <span class="help-block">登録のメリットはこれといってありません。<br />ユーザー名とパスワードを設定してください。</span>
                <button type="submit" class="btn btn-success">新規登録</button>
            </form>
        </div>
    </div>
</div>

<?php echo $content_footer ?>
</body>
</html>
