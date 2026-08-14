<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connection Problem · {{ config('app.name') }}</title>
<style>
  body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f4f4f5; color: #1f2937; padding: 24px; }
  .box { max-width: 440px; text-align: center; background: #fff; border-radius: 12px; padding: 40px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .icon { font-size: 42px; margin-bottom: 16px; }
  h1 { font-size: 19px; margin: 0 0 10px; }
  p { font-size: 14px; color: #6b7280; line-height: 1.5; margin: 0 0 20px; }
  button { border: 1px solid #d1d5db; background: #fff; color: #1f2937; padding: 9px 18px; border-radius: 8px; font-size: 14px; cursor: pointer; }
  button:hover { background: #f9fafb; }
</style>
</head>
<body>
  <div class="box">
    <div class="icon">📡</div>
    <h1>Unable to connect to the Internet</h1>
    <p>
      This device couldn't reach the database. If you're running this app
      locally, please check your internet connection — the app needs it to
      reach the server database — then try again.
    </p>
    <button onclick="window.location.reload()">Try Again</button>
  </div>
</body>
</html>
