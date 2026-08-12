<div class="modal fade" id="modalGmailInstructions" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-google me-2" style="color:var(--color-danger);"></i>Gmail Integration — Setup Instructions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted-c">
          Gmail Integration uses real Google sign-in (OAuth), not a simple API
          key. This is a two-step setup: an <strong>admin</strong> creates one
          Google Cloud project/OAuth client and saves its Client ID/Secret
          once (Settings → Google OAuth Client, stored encrypted in the
          database, not in a file) — after that, <strong>every teammate</strong>
          connects their own Gmail from their own Settings → My Gmail card.
          The steps below are identical except for the redirect URI, which
          differs between a live domain and a local XAMPP install.
        </p>

        <ol class="small ps-3 mb-4">
          <li class="mb-2">Go to <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a>, create (or reuse) a project.</li>
          <li class="mb-2">Enable the <strong>Gmail API</strong>: APIs &amp; Services → Library → search "Gmail API" → Enable.</li>
          <li class="mb-2">Configure the <strong>OAuth consent screen</strong> (External is fine). Under "Test users", add your own Google account — this avoids Google's full app-review process while you're just connecting your own inbox.</li>
          <li class="mb-2">Credentials → Create Credentials → <strong>OAuth client ID</strong> → Application type <strong>Web application</strong>.</li>
          <li class="mb-2">Under "Authorized redirect URIs", add the URI for your environment — see the two tabs below (you can add both to the same client at once).</li>
          <li class="mb-2">Copy the generated <strong>Client ID</strong> and <strong>Client Secret</strong>.</li>
        </ol>

        <ul class="nav nav-tabs small" id="gmailInstructionsTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-live-btn" data-bs-toggle="tab" data-bs-target="#tab-live" type="button" role="tab">Live / Production Hosting</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-local-btn" data-bs-toggle="tab" data-bs-target="#tab-local" type="button" role="tab">Local (XAMPP)</button>
          </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 mb-3" style="border-radius:0 0 var(--radius-md) var(--radius-md);">
          {{-- Live / Production --}}
          <div class="tab-pane fade show active" id="tab-live" role="tabpanel">
            <p class="small mb-2">Redirect URI to add in Google Cloud Console (this is this site's real address):</p>
            <div class="input-group input-group-sm mb-3">
              <input type="text" class="form-control" id="gmailRedirectLive" value="{{ $gmailCallbackUrl }}" readonly>
              <button class="btn btn-outline-c" type="button" data-copy-target="gmailRedirectLive"><i class="bi bi-clipboard"></i> Copy</button>
            </div>
            <p class="small text-muted-c mb-0">
              Admin: paste the Client ID and Client Secret into the
              <strong>Google OAuth Client</strong> card and click
              <strong>Save Credentials</strong> — once. Then anyone,
              including you, clicks <strong>Connect Gmail</strong> in their
              own <strong>My Gmail</strong> card. No <code>.env</code>
              editing or redeploy needed.
            </p>
          </div>

          {{-- Local / XAMPP --}}
          <div class="tab-pane fade" id="tab-local" role="tabpanel">
            <p class="small mb-2">
              Google only allows non-HTTPS redirect URIs for <code>localhost</code>/<code>127.0.0.1</code>
              — so for XAMPP, open this app via <code>http://localhost/...</code>
              (not a custom hostname like <code>vivek-crm.local</code>, unless it has real HTTPS).
              Also set <code>APP_URL=http://localhost</code> in your local
              <code>.env</code> so the redirect URI this app generates matches.
            </p>
            <p class="small mb-2">Redirect URI to add in Google Cloud Console:</p>
            <div class="input-group input-group-sm mb-3">
              <input type="text" class="form-control" id="gmailRedirectLocal" value="http://localhost/settings/gmail/callback" readonly>
              <button class="btn btn-outline-c" type="button" data-copy-target="gmailRedirectLocal"><i class="bi bi-clipboard"></i> Copy</button>
            </div>
            <p class="small text-muted-c mb-0">
              Adjust the port if XAMPP's Apache isn't on the default port 80,
              e.g. <code>http://localhost:8080/settings/gmail/callback</code>
              (and <code>APP_URL</code> to match). Then (admin) save the same
              Client ID/Secret in the Google OAuth Client card, and click
              <strong>Connect Gmail</strong> in your own My Gmail card.
            </p>
          </div>
        </div>

        <p class="small text-muted-c mb-0">
          Tip: you can add <em>both</em> redirect URIs to the same Google OAuth
          client at the same time, so local testing and your live site work
          without creating separate credentials.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light-c" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-copy-target'));
      input.select();
      input.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(input.value).then(function () {
        if (typeof showToast === 'function') { showToast('Copied to clipboard.', 'success'); }
      }).catch(function () {
        document.execCommand('copy');
      });
    });
  });
});
</script>
@endpush
