<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Opening WhatsApp…</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: system-ui, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; background:#f4f4f5; color:#1f2937; }
  .box { text-align:center; }
  .spinner { width:34px; height:34px; border:3px solid #d1d5db; border-top-color:#25D366; border-radius:50%; margin:0 auto 16px; animation:spin 0.8s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  a { color:#25D366; font-weight:600; }
</style>
</head>
<body>
  <div class="box">
    <div class="spinner"></div>
    <p>Opening WhatsApp…</p>
    <p><a href="{{ $webLink }}" id="fallbackLink">Click here if nothing happens</a></p>
  </div>
  <script>
    // Try the fast native-app link first; if the page is still visible
    // shortly after (nothing intercepted it — no WhatsApp app registered
    // for whatsapp://), fall back to the universal wa.me link.
    var opened = false;
    window.addEventListener('blur', function () { opened = true; });
    window.location = @json($appLink);
    setTimeout(function () {
      if (!opened && !document.hidden) {
        window.location = @json($webLink);
      }
    }, 800);
  </script>
</body>
</html>
