<html>
<head>
    <title>phpCAS simple client</title>
</head>
<body>
<h1>Successfull Authentication!</h1>

<dl style='border: 1px dotted; padding: 5px;'>
    <dt>Current script</dt><dd>{{basename($_SERVER['SCRIPT_NAME'])}}</dd>
    <dt>session_name():</dt><dd> {{session_name()}}</dd>
    <dt>session_id():</dt><dd> {{session_id()}}</dd>
</dl>


<p>the user's login is <b>{{phpCAS::getUser()}}</b>.</p>
<p>phpCAS version is <b>{{phpCAS::getVersion()}}</b>.</p>
</body>
</html>