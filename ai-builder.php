<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Generator - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #ffffff; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #1e1e1e; border-bottom: 1px solid #333; }
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; transition: 0.3s; }
        .brand-name:hover { opacity: 0.8; }
        .pc-text { color: #add8e6; }
        
        .builder-card { background: #1e1e1e; border: 1px solid #333; border-radius: 15px; padding: 25px; color: #ffffff; }
        
        .form-label { color: #add8e6; font-weight: bold; font-size: 0.9rem; letter-spacing: 0.5px; }
        
        .form-control, .form-select { 
            background: #2b2b2b; 
            border: 1px solid #444; 
            color: white; 
            padding: 12px;
        }
        .form-control:focus { background: #333; color: white; border-color: #add8e6; box-shadow: none; }

        .form-control::placeholder {
            color: #cccccc !important; 
            opacity: 1; 
        }
        
        .part-card { background: #252525; border: 1px solid #444; border-radius: 10px; margin-bottom: 12px; padding: 15px; }
        .text-primary { color: #add8e6 !important; }
        .text-muted { color: #b0b0b0 !important; }

        .btn-back { color: #add8e6; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .btn-back:hover { color: #ffffff; }

        /* Animation for list */
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 mb-4">
    <a href="home.php" class="text-decoration-none">
        <span class="brand-name text-white text-uppercase">Synthesis <span class="pc-text">PC</span></span>
    </a>
    <div class="d-flex align-items-center">
        <span class="text-muted me-3 small">User: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="mb-3">
                <a href="home.php" class="btn-back">← Back to Dashboard</a>
            </div>

            <div class="builder-card mb-4">
                <h3 class="mb-4 fw-bold text-uppercase">AI Build Generator</h3>
                <form id="aiForm" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">BUDGET (PHP)</label>
                        <input type="number" id="budgetInput" class="form-control" placeholder="Enter amount (e.g. 50000)" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">BUDGET STRATEGY</label>
                        <select id="strategyInput" class="form-select">
                            <option value="value">Budget-Friendly (Max Value)</option>
                            <option value="maxout">Maximize Budget (Performance Priority)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PURPOSE</label>
                        <select id="purposeInput" class="form-select">
                            <option value="ultra">Ultra / High End</option>
                            <option value="gaming">Gaming</option>
                            <option value="editing">Editing / Rendering</option>
                            <option value="office">Office / School / Work</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PARTS ERA</label>
                        <select id="eraInput" class="form-select">
                            <option value="all">All Generations</option>
                            <option value="2020">Modern (2020+)</option>
                            <option value="2010">Legacy / Budget</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" id="generateBtn" class="btn btn-primary w-100 fw-bold py-2" style="background-color: #0d6efd; border: none;">GENERATE BEST BUILD</button>
                    </div>
                </form>
            </div>

            <div id="loading" class="text-center" style="display:none; padding: 40px;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-primary fw-bold">Synthesis AI is analyzing parts and cooling solutions...</p>
            </div>
            <div id="resultArea"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('aiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const budget = document.getElementById('budgetInput').value;
    const strategy = document.getElementById('strategyInput').value; // Kinuha ang bagong value ng strategy
    const purpose = document.getElementById('purposeInput').value;
    const era = document.getElementById('eraInput').value;
    const btn = document.getElementById('generateBtn');
    const loading = document.getElementById('loading');
    const resultArea = document.getElementById('resultArea');

    btn.disabled = true;
    loading.style.display = 'block';
    resultArea.innerHTML = '';
     
    const dbParts = <?php
        $host = "sql310.infinityfree.com";
        $user = "if0_41890353";
        $pass = "FinalProject001"; 
        $dbname = "if0_41890353_pc_builder_db";
        $conn = new mysqli($host, $user, $pass, $dbname);
        
        if ($conn->connect_error) { 
            echo "[]"; 
        } else {
            $sql = "SELECT id, name, type AS category, brand, price, stock, performance_tag, socket, ram_gen FROM components WHERE stock > 0";
            $res = $conn->query($sql);
            $p = [];
            while($row = $res->fetch_assoc()) {
                $row['price'] = (float)$row['price'];
                $p[] = $row;
            }
            echo json_encode($p);
            $conn->close();
        }
    ?>;

    try {
        const response = await fetch('https://pc-builder-ai-1.onrender.com/generate-build', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            // Isinama ang 'strategy' sa JSON payload para mabasang mabuti ng Python backend algorithm mo
            body: JSON.stringify({ budget, strategy, purpose, era, parts: dbParts })
        });

        const data = await response.json();
        if (data.status === 'success') {
            renderBuild(data);
        } else {
            resultArea.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
        }
    } catch (err) {
        resultArea.innerHTML = `<div class="alert alert-danger">Error connecting to Synthesis AI server. Check your connection or API status.</div>`;
    } finally {
        btn.disabled = false;
        loading.style.display = 'none';
    }
});

function renderBuild(data) {
    let html = `<div class="builder-card fade-in">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-0">Recommended Build</h4>
                        <small class="text-muted">Optimized for ${data.suggested_tier}</small>
                    </div>
                    <div class="text-end">
                        <h3 class="text-primary fw-bold mb-0">₱${data.total_spent.toLocaleString()}</h3>
                        <small class="text-muted">Total Estimated Price</small>
                    </div>
                </div><hr class="border-secondary">`;
    
    const order = ["CPU", "MOTHERBOARD", "RAM", "GPU", "SSD", "PSU", "CASE", "CPU COOLER", "CASE FANS", "MONITOR"];
    const buildKeys = Object.keys(data.build).sort((a, b) => {
        return (order.indexOf(a) === -1 ? 99 : order.indexOf(a)) - (order.indexOf(b) === -1 ? 99 : order.indexOf(b));
    });

    for (const category of buildKeys) {
        const item = data.build[category];
        html += `
            <div class="part-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-primary text-uppercase fw-bold" style="font-size: 0.7rem;">${category}</small>
                        <h5 class="mb-0 text-white">${item.name}</h5>
                        <small class="text-muted">${item.brand || 'Standard Edition'}</small>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-white">₱${(item.price || 0).toLocaleString()}</span>
                    </div>
                </div>
            </div>`;
    }
    html += `</div>`;
    document.getElementById('resultArea').innerHTML = html;
}
</script>

</body>
</html>
