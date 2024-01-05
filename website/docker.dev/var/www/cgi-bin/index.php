<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Configuration Information</title>
</head>
<body>
<div>
  <a href="/cgi-bin/php.php">phpinfo();</a>
</div>
<br>
<div>
  <?php
  $startDebug = [
    'document.cookie="start_debug=1;path=/;"',
    'document.cookie="debug_session_id=' . addslashes($_ENV['DEBUG_KEY']) . ';path=/;"',
    'document.cookie="debug_host=' . addslashes($_ENV['DEBUG_HOST']) . ';path=/;"',
    'document.cookie="debug_port=' . addslashes($_ENV['DEBUG_PORT']) . ';path=/;"',
    'document.cookie="XDEBUG_SESSION=' . addslashes($_ENV['DEBUG_KEY']) . ';path=/;"',
  ];
  ?>
  <a href="javascript:(function(){<?= urlencode(implode(';', $startDebug)) ?>})()">Start Debug</a>
</div>
<div>
  <?php
  $stopDebug = [
    'document.cookie="start_debug=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;Max-Age=0;"',
    'document.cookie="debug_session_id=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;Max-Age=0;"',
    'document.cookie="debug_host=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;Max-Age=0;"',
    'document.cookie="debug_port=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;Max-Age=0;"',
    'document.cookie="XDEBUG_SESSION=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;Max-Age=0;"',
  ];
  ?>
  <a href="javascript:(function(){<?= urlencode(implode(';', $stopDebug)) ?>})()">Stop Debug</a>
</div>
<br>
<div>
  <form method="get" action="db-replace.php">
    <strong>Replace domain name in the database</strong><br>
    For example, if the production domain name is <strong>website.com</strong>,<br>
    and the local domain name is <strong>website.localhost</strong>,<br>
    then the first field should be <strong>website.com</strong>,<br>
    and the second one should be <strong>website.localhost</strong>.<br>
    <input type="text" name="from" required placeholder="Domain name to find">
    <input type="text" name="to" value="<?= htmlentities($_SERVER['HTTP_HOST']) ?>" required
           placeholder="Domain name to use">
    <input type="submit" value="Submit">
  </form>
</div>
</body>
</html>
