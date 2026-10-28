document.addEventListener('DOMContentLoaded', async () => {
  const urlBox = document.getElementById('activeUrl');
  const scanBtn = document.getElementById('scanBtn');
  const resultDiv = document.getElementById('result');
  const statusBadge = document.getElementById('statusBadge');
  const riskScore = document.getElementById('riskScore');

  // Get active tab URL from Chrome API
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
  const currentUrl = tab ? tab.url : '';
  urlBox.innerText = currentUrl || 'No active web URL found.';

  scanBtn.addEventListener('click', async () => {
    if (!currentUrl || !currentUrl.startsWith('http')) {
      alert('Can only scan HTTP/HTTPS web links.');
      return;
    }

    scanBtn.innerText = 'Scanning ML Service...';
    scanBtn.disabled = true;

    try {
      // Route through PHP Gateway API
      const response = await fetch('http://localhost/phishing_detector/api_scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: currentUrl })
      });

      const data = await response.json();

      resultDiv.style.display = 'block';
      resultDiv.className = data.status.toLowerCase();
      
      statusBadge.innerText = data.status;
      statusBadge.className = `badge badge-${data.status.toLowerCase()}`;
      riskScore.innerText = `${Math.round(data.risk_score)}%`;

    } catch (err) {
      alert('Failed to connect to scanner service. Ensure Laragon Apache and Uvicorn port 8000 are active.');
    } finally {
      scanBtn.innerText = 'Scan Tab URL';
      scanBtn.disabled = false;
    }
  });
});