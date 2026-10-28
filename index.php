<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Phishing & Threat Detector</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <div class="max-w-5xl mx-auto p-6">
        
        <!-- Header -->
        <header class="text-center my-8">
            <h1 class="text-4xl font-extrabold text-indigo-400">🛡️ AI Phishing & URL Threat Detector</h1>
            <p class="text-slate-400 mt-2">Paste any website link below to run real-time lexical and machine learning risk analysis.</p>
        </header>

        <!-- Analytics Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex flex-col justify-between">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Scanned Links</span>
                <p id="totalScansStat" class="text-3xl font-extrabold text-indigo-400 mt-2">0</p>
            </div>
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex flex-col justify-between">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Average System Risk</span>
                <p id="avgRiskStat" class="text-3xl font-extrabold text-amber-400 mt-2">0%</p>
            </div>
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex flex-col justify-between">
                <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Threat Detection Status</span>
                <p id="systemStatusStat" class="text-3xl font-extrabold text-emerald-400 mt-2">Active</p>
            </div>
        </div>

        <!-- Main Workspace Grid (Scanner + Chart) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Scanner & Results (Spans 2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Scanner Input Card -->
                <div class="bg-slate-800 p-6 rounded-xl shadow-lg border border-slate-700">
                    <form id="scanForm" class="flex flex-col md:flex-row gap-4">
                        <input type="url" id="urlInput" placeholder="Enter URL (e.g., https://paypal-security-login.com)" required 
                               class="flex-1 bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-slate-100 focus:outline-none focus:border-indigo-500">
                        <button type="submit" id="scanBtn" class="bg-indigo-600 hover:bg-indigo-500 font-bold px-6 py-3 rounded-lg transition-colors whitespace-nowrap">
                            Analyze Link
                        </button>
                    </form>
                </div>

                <!-- Live Result Box -->
                <div id="resultCard" class="hidden p-6 rounded-xl border"></div>

                <!-- Recent Scans History -->
                <div class="bg-slate-800 rounded-xl p-5 border border-slate-700">
                    <h2 class="text-lg font-bold mb-4 text-slate-200">Recent System Scans</h2>
                    <div id="recentScans">
                        <p class="text-slate-400 text-sm">No recent scans yet.</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Chart.js Visualizer (Spans 1 col) -->
            <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 flex flex-col items-center justify-center">
                <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4 w-full text-left">Threat Distribution</h3>
                <div class="w-full max-w-[260px]">
                    <canvas id="threatChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
        let threatChart = null;

        // Initialize and Update Chart.js + Analytics Metrics
        async function loadAnalytics() {
            try {
                const res = await fetch('api_analytics.php');
                const data = await res.json();

                document.getElementById('totalScansStat').innerText = data.total_scans;
                document.getElementById('avgRiskStat').innerText = `${data.avg_risk}%`;

                const ctx = document.getElementById('threatChart').getContext('2d');
                const chartData = [
                    data.status_counts.Safe || 0,
                    data.status_counts.Suspicious || 0,
                    data.status_counts.Malicious || 0
                ];

                if (threatChart) {
                    threatChart.data.datasets[0].data = chartData;
                    threatChart.update();
                } else {
                    threatChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Safe', 'Suspicious', 'Malicious'],
                            datasets: [{
                                data: chartData,
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: '#94a3b8', font: { family: 'sans-serif', size: 12 } }
                                }
                            }
                        }
                    });
                }
            } catch (err) {
                console.error('Failed to load analytics:', err);
            }
        }

        // Fetch and Render Recent Scans History
        async function loadRecentScans() {
            try {
                const res = await fetch('api_get_scans.php');
                const scans = await res.json();
                const recentDiv = document.getElementById('recentScans');

                if (scans.length === 0) {
                    recentDiv.innerHTML = '<p class="text-slate-400 text-sm">No recent scans yet.</p>';
                    return;
                }

                recentDiv.innerHTML = scans.map(s => `
                    <div class="flex items-center justify-between border-b border-slate-700/60 py-3 last:border-b-0">
                        <div class="overflow-hidden pr-4">
                            <p class="text-sm font-semibold truncate text-slate-200">${s.url_scanned}</p>
                            <p class="text-xs text-slate-400">${s.scanned_at}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full ${s.status === 'Safe' ? 'bg-emerald-600/30 text-emerald-400 border border-emerald-500/40' : (s.status === 'Suspicious' ? 'bg-amber-600/30 text-amber-400 border border-amber-500/40' : 'bg-red-600/30 text-red-400 border border-red-500/40')}">
                                ${s.status}
                            </span>
                            <span class="text-sm font-extrabold text-slate-300 w-12 text-right">${Math.round(s.risk_score)}%</span>
                        </div>
                    </div>
                `).join('');
            } catch (err) {
                console.error('Failed to load scan history:', err);
            }
        }

        // Form Submit Handler
        document.getElementById('scanForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const urlInput = document.getElementById('urlInput').value;
            const scanBtn = document.getElementById('scanBtn');
            const resultCard = document.getElementById('resultCard');

            scanBtn.innerText = 'Analyzing...';
            scanBtn.disabled = true;

            try {
                const response = await fetch('api_scan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url: urlInput })
                });

                const data = await response.json();
                
                resultCard.classList.remove('hidden', 'bg-red-950/40', 'border-red-500', 'bg-amber-950/40', 'border-amber-500', 'bg-emerald-950/40', 'border-emerald-500');
                
                if (data.status === 'Malicious') {
                    resultCard.classList.add('bg-red-950/40', 'border-red-500');
                } else if (data.status === 'Suspicious') {
                    resultCard.classList.add('bg-amber-950/40', 'border-amber-500');
                } else {
                    resultCard.classList.add('bg-emerald-950/40', 'border-emerald-500');
                }

                resultCard.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider px-3 py-1 rounded-full ${data.status === 'Safe' ? 'bg-emerald-600' : (data.status === 'Suspicious' ? 'bg-amber-600' : 'bg-red-600')} text-white">
                                ${data.status}
                            </span>
                            <h3 class="text-lg font-bold mt-2 break-all text-slate-100">${data.url}</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-extrabold ${data.status === 'Safe' ? 'text-emerald-400' : (data.status === 'Suspicious' ? 'text-amber-400' : 'text-red-400')}">${data.risk_score}%</span>
                            <p class="text-xs text-slate-400">Risk Score</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-700/60">
                        <p class="text-sm font-semibold text-slate-300">Extracted Machine Learning Signals:</p>
                        <ul class="list-disc list-inside text-sm text-slate-400 mt-1">
                            ${data.threats.map(t => `<li>${t}</li>`).join('')}
                        </ul>
                    </div>
                `;

                // Reload History & Analytics Chart automatically
                loadRecentScans();
                loadAnalytics();

            } catch (err) {
                alert('Analysis failed. Check Python service.');
            } finally {
                scanBtn.innerText = 'Analyze Link';
                scanBtn.disabled = false;
            }
        });

        // Load data on page launch
        loadRecentScans();
        loadAnalytics();
    </script>
</body>
</html>