<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$host = "sql310.infinityfree.com";
$user = "if0_41890353";
$pass = "FinalProject001"; 
$dbname = "if0_41890353_pc_builder_db";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save') {
    header('Content-Type: application/json');
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['parts'])) {
        echo json_encode(["status" => "error", "message" => "Invalid blueprint or component payload"]);
        exit();
    }

    $build_name = $conn->real_escape_string($data['build_name']);
    $total = floatval($data['total_price']);
    $edit_id = isset($data['edit_id']) ? intval($data['edit_id']) : 0;
    
    $ram_qty = isset($data['ram_quantity']) ? intval($data['ram_quantity']) : 1;
    if ($ram_qty < 1 || $ram_qty > 4) $ram_qty = 1;
    
    if ($edit_id > 0) {
        $sql_build = "UPDATE builds SET name = '$build_name', total_price = $total WHERE build_id = $edit_id AND user_id = $user_id";
        if ($conn->query($sql_build)) {
            $current_build_id = $edit_id;
            $conn->query("DELETE FROM build_items WHERE build_id = $current_build_id");
        } else {
            echo json_encode(["status" => "error", "message" => "Update layout failed: " . $conn->error]);
            exit();
        }
    } else {
        $sql_build = "INSERT INTO builds (user_id, name, total_price) VALUES ($user_id, '$build_name', $total)";
        if ($conn->query($sql_build)) {
            $current_build_id = $conn->insert_id;
        } else {
            echo json_encode(["status" => "error", "message" => "Insert framework failed: " . $conn->error]);
            exit();
        }
    }
    
    $success = true;
    foreach ($data['parts'] as $category => $component_id) {
        if (!empty($component_id)) {
            $c_id = intval($component_id);
            
            $qty = ($category === 'RAM') ? $ram_qty : 1;
            
            $sql_item = "INSERT INTO build_items (build_id, component_id, quantity) VALUES ($current_build_id, $c_id, $qty)";
            if (!$conn->query($sql_item)) {
                $success = false;
                $error_msg = $conn->error;
                break;
            }
        }
    }
    
    if ($success) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Structure mapped but components failed: " . $error_msg]);
    }
    exit();
}

$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT name, total_price FROM builds WHERE build_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $res_b = $stmt->get_result();
    
    if ($res_b && $row_b = $res_b->fetch_assoc()) {
        $edit_data = [
            'edit_id' => $edit_id,
            'build_name' => $row_b['name'],
            'total_price' => $row_b['total_price'],
            'parts' => [],
            'ram_quantity' => 1]
        ];
        
        $items_stmt = $conn->prepare("SELECT component_id, quantity FROM build_items WHERE build_id = ?");
        $items_stmt->bind_param("i", $edit_id);
        $items_stmt->execute();
        $res_i = $items_stmt->get_result();
        
        while ($row_i = $res_i->fetch_assoc()) {
            $edit_data['parts'][] = intval($row_i['component_id']);
            
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Architect - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #ffffff; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #1e1e1e; border-bottom: 1px solid #333; }
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
        .pc-text { color: #add8e6; }
        .builder-card { background: #1e1e1e; border: 1px solid #333; border-radius: 15px; padding: 25px; }
        .part-item-card { 
            background: #252525; border: 1px solid #444; border-radius: 12px; 
            transition: 0.3s; cursor: pointer; height: 100%; display: flex; flex-direction: column; justify-content: space-between;
        }
        .part-item-card:hover { border-color: #add8e6; transform: translateY(-3px); background: #2b2b2b; }
        .part-item-card.selected { border-color: #0d6efd; background: rgba(13, 110, 253, 0.15); box-shadow: 0 0 15px rgba(13, 110, 253, 0.3); }
        .text-primary-custom { color: #add8e6; }
        .step-indicator { font-size: 0.85rem; letter-spacing: 1px; font-weight: bold; color: #add8e6; text-transform: uppercase; }
        .sidebar-summary { background: #1e1e1e; border: 1px solid #333; border-radius: 15px; padding: 20px; position: sticky; top: 20px; }
        
        #summaryList .d-flex { color: #bbbbbb !important; }
        #summaryList span:first-child { color: #aaaaaa !important; }
        #summaryList span[id^="summary-"] { color: #ffffff !important; }
        .sidebar-summary h4 { color: #ffffff !important; }
        .sidebar-summary span.text-uppercase { color: #aaaaaa !important; }

        #buildNameInput {
            background-color: #252525 !important;
            color: #ffffff !important;
            border: 1px solid #444 !important;
        }
        #buildNameInput::placeholder { color: #888888 !important; }
        #buildNameInput:focus {
            border-color: #add8e6 !important;
            box-shadow: 0 0 0 0.25rem rgba(173, 216, 230, 0.25) !important;
        }
        /* Custom selector styling para sa RAM quantity box */
        .qty-selector {
            background-color: #121212 !important;
            color: #ffffff !important;
            border: 1px solid #444 !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 mb-4">
    <a href="home.php" class="text-decoration-none">
        <span class="brand-name text-white text-uppercase">Synthesis <span class="pc-text">PC</span></span>
    </a>
</nav>

<div class="container-fluid px-5">
    <div class="mb-3">
        <a href="<?php echo isset($_GET['edit_id']) ? 'saved-builds.php' : 'home.php'; ?>" class="text-decoration-none text-primary-custom">
            ← <?php echo isset($_GET['edit_id']) ? 'Cancel and Return to Archive' : 'Return to Control Center'; ?>
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="builder-card">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <div>
                        <span id="currentStepBadge" class="step-indicator"></span>
                        <h3 id="currentCategoryTitle" class="fw-bold text-uppercase mt-1 mb-0"></h3>
                    </div>
                    <div id="navigationButtons">
                        <button id="prevBtn" class="btn btn-outline-secondary btn-sm px-3 me-2" disabled>Back</button>
                        <button id="nextBtn" class="btn btn-primary btn-sm px-3" disabled>Next Step →</button>
                    </div>
                </div>

                <div id="ramQtyContainer" class="mb-4 p-3 bg-dark border border-secondary rounded-3" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="fw-bold text-white mb-1">Modules Architecture Multiplier</h6>
                            <p class="text-muted small mb-0">Piliin kung ilang sticks ng memory kit na ito ang nais mong isalpak (Max 4 DIMM slots).</p>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-secondary text-white border-secondary small">Quantity:</span>
                                <select id="ramQtySelect" class="form-select qty-selector fw-bold text-info">
                                    <option value="1">1 Stick (Single Channel)</option>
                                    <option value="2" selected>2 Sticks (Dual Channel Config)</option>
                                    <option value="3">3 Sticks (Triple Channel Config)</option>
                                    <option value="4">4 Sticks (Fully Populated Matrix)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="partsCatalog" class="row g-3"></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-summary">
                <h4 class="fw-bold text-uppercase border-bottom border-secondary pb-2 mb-3">Live Configuration Log</h4>
                <div id="summaryList" class="mb-4">
                    <div class="d-flex justify-content-between small my-2"><span>Motherboard:</span><span id="summary-MOTHERBOARD">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Processor (CPU):</span><span id="summary-CPU">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Memory (RAM):</span><span id="summary-RAM">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Graphics (GPU):</span><span id="summary-GPU">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Storage (SSD):</span><span id="summary-SSD">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Power Supply (PSU):</span><span id="summary-PSU">Not Selected</span></div>
                    <div class="d-flex justify-content-between small my-2"><span>Enclosure (Case):</span><span id="summary-CASE">Not Selected</span></div>
                </div>
                <div class="border-top border-secondary pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-uppercase small">Aggregated Total</span>
                        <h3 id="liveTotalPrice" class="fw-bold text-primary-custom mb-0">₱0.00</h3>
                    </div>
                    <div id="saveSection" style="display: none;">
                        <input type="text" id="buildNameInput" class="form-control mb-2" placeholder="Assign Build Identity Name" required>
                        <button id="saveBuildBtn" class="btn btn-success w-100 fw-bold py-2">COMMIT BUILD TO ARCHIVE</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const allComponents = <?php
    $sql = "SELECT id, name, type AS category, brand, price, socket, ram_gen, performance_tag FROM components WHERE stock > 0";
    $res = $conn->query($sql);
    $arr = [];
    while($row = $res->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['price'] = (float)$row['price'];
        $arr[] = $row;
    }
    echo json_encode($arr);
?>;

const editModeData = <?php echo $edit_data ? json_encode($edit_data) : 'null'; ?>;

// Kunin din ang mga items pati quantity mula sa build kung nag-e-edit
const rawBuildItems = <?php 
    if (isset($_GET['edit_id'])) {
        $id = intval($_GET['edit_id']);
        $res_items = $conn->query("SELECT component_id, quantity FROM build_items WHERE build_id = $id");
        $b_items = [];
        while($r = $res_items->fetch_assoc()){
            $b_items[] = ['component_id' => (int)$r['component_id'], 'quantity' => (int)$r['quantity']];
        }
        echo json_encode($b_items);
    } else {
        echo '[]';
    }
    $conn->close();
?>;

const steps = ["MOTHERBOARD", "CPU", "RAM", "GPU", "SSD", "PSU", "CASE"];
let currentStepIdx = 0;

let userSelection = {
    parts: {},
    meta: {},
    ram_quantity: 2 
};

function initBuilder() {
    if (editModeData && editModeData.parts) {
        editModeData.parts.forEach(componentId => {
            const componentObj = allComponents.find(p => p.id === componentId);
            if (componentObj && componentObj.category) {
                let catKey = componentObj.category.toString().trim().toUpperCase();
                if (!steps.includes(catKey) && steps.includes(catKey + 'S')) catKey = catKey + 'S';
                if (!steps.includes(catKey) && catKey.endsWith('S') && steps.includes(catKey.slice(0, -1))) catKey = catKey.slice(0, -1);
                
                userSelection.parts[catKey] = componentId;
                userSelection.meta[catKey] = componentObj;

                const savedItemInfo = rawBuildItems.find(i => i.component_id === componentId);
                if (catKey === "RAM" && savedItemInfo) {
                    userSelection.ram_quantity = savedItemInfo.quantity;
                    document.getElementById('ramQtySelect').value = savedItemInfo.quantity;
                }
            }
        });
        
        document.getElementById('buildNameInput').value = editModeData.build_name;
        updateSummaryDOM();
        calculateRunningTotalPrice();
    }

    renderCurrentStep();
    
    document.getElementById('ramQtySelect').addEventListener('change', function() {
        userSelection.ram_quantity = parseInt(this.value);
        updateSummaryDOM();
        calculateRunningTotalPrice();
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
        if(currentStepIdx < steps.length - 1) {
            currentStepIdx++;
            renderCurrentStep();
        } else {
            showFinalSaveState();
        }
    });

    document.getElementById('prevBtn').addEventListener('click', () => {
        if(currentStepIdx > 0) {
            currentStepIdx--;
            document.getElementById('saveSection').style.display = 'none';
            renderCurrentStep();
        }
    });

    document.getElementById('saveBuildBtn').addEventListener('click', commitBuildToDatabase);
}

function updateSummaryDOM() {
    steps.forEach(catKey => {
        const componentObj = userSelection.meta[catKey];
        const summarySpan = document.getElementById(`summary-${catKey}`);
        if (summarySpan && componentObj) {
            if (catKey === "RAM") {
                let qtyMultiplierText = userSelection.ram_quantity > 1 ? ` [x${userSelection.ram_quantity} Sticks]` : ` [x1 Stick]`;
                let collectiveRamPrice = componentObj.price * userSelection.ram_quantity;
                summarySpan.innerText = `${componentObj.name}${qtyMultiplierText} (₱${collectiveRamPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
            } else {
                summarySpan.innerText = `${componentObj.name} (₱${componentObj.price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
            }
            summarySpan.className = "fw-bold text-white text-truncate d-inline-block";
        }
    });
}

function renderCurrentStep() {
    const currentCategory = steps[currentStepIdx];
    document.getElementById('currentStepBadge').innerText = `Step ${currentStepIdx + 1} of ${steps.length}`;
    document.getElementById('currentCategoryTitle').innerText = `Select ${currentCategory}`;
    
    document.getElementById('prevBtn').disabled = currentStepIdx === 0;
    document.getElementById('nextBtn').disabled = !userSelection.parts[currentCategory];
    
    // FIX UI: Lilitaw lang ang RAM multiplier module selector box kung kasalukuyang nasa RAM choice index step ang user
    if (currentCategory === "RAM") {
        document.getElementById('ramQtyContainer').style.display = 'block';
    } else {
        document.getElementById('ramQtyContainer').style.display = 'none';
    }

    if(currentStepIdx === steps.length - 1 && userSelection.parts[currentCategory]) {
        document.getElementById('nextBtn').innerText = "Finish Configuration";
    } else {
        document.getElementById('nextBtn').innerText = "Next Step →";
    }

    let filteredOptions = allComponents.filter(p => {
        if (!p.category) return false;
        let dbCat = p.category.toString().trim().toUpperCase();
        let stepCat = currentCategory.toString().trim().toUpperCase();
        return dbCat === stepCat || dbCat === stepCat + 'S' || dbCat + 'S' === stepCat;
    });

    if (currentCategory === "CPU" && userSelection.meta["MOTHERBOARD"]) {
        const allowedSocket = userSelection.meta["MOTHERBOARD"].socket ? userSelection.meta["MOTHERBOARD"].socket.toString().trim().toUpperCase() : '';
        filteredOptions = filteredOptions.filter(p => {
            const pSocket = p.socket ? p.socket.toString().trim().toUpperCase() : '';
            return pSocket === allowedSocket;
        });
    }
    
    if (currentCategory === "RAM" && userSelection.meta["MOTHERBOARD"]) {
        const allowedRamGen = userSelection.meta["MOTHERBOARD"].ram_gen ? userSelection.meta["MOTHERBOARD"].ram_gen.toString().trim().toUpperCase() : '';
        filteredOptions = filteredOptions.filter(p => {
            const pRamGen = p.ram_gen ? p.ram_gen.toString().trim().toUpperCase() : '';
            return pRamGen === allowedRamGen;
        });
    }

    const catalogContainer = document.getElementById('partsCatalog');
    catalogContainer.innerHTML = '';

    if(filteredOptions.length === 0) {
        catalogContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-warning border-dashed bg-transparent text-warning">
                    ⚠️ No compatible ${currentCategory} variants available for the current system configuration framework.
                </div>
            </div>`;
        document.getElementById('nextBtn').disabled = false;
        return;
    }

    filteredOptions.forEach(part => {
        const isSelected = userSelection.parts[currentCategory] == part.id;
        
        let specsText = '';
        if (part.socket) specsText += `Socket: ${part.socket}`;
        if (part.ram_gen) specsText += (specsText ? ' | ' : '') + `Gen: ${part.ram_gen}`;
        if (part.performance_tag) specsText += (specsText ? ' | ' : '') + `Tag: ${part.performance_tag}`;
        if (!specsText) specsText = 'Standard Desktop Specification Component';

        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <div class="card part-item-card p-3 ${isSelected ? 'selected' : ''}" data-part-id="${part.id}">
                <div>
                    <span class="badge bg-secondary mb-2">${part.brand}</span>
                    <h5 class="fw-bold fs-6 text-white mb-1">${part.name}</h5>
                    <p class="text-muted small mb-3">${specsText}</p>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-dark">
                    <span class="fw-bold text-primary-custom">₱${part.price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    <button class="btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-primary'} py-1 px-3">
                        ${isSelected ? 'Selected' : 'Choose'}
                    </button>
                </div>
            </div>
        `;
        catalogContainer.appendChild(col);
    });

    document.querySelectorAll('#partsCatalog .part-item-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const partId = parseInt(this.getAttribute('data-part-id'));
            selectComponent(currentCategory, partId);
        });
    });
}

function selectComponent(category, partId) {
    const componentObj = allComponents.find(p => p.id == partId);
    if(!componentObj) return;
    
    if(category === "MOTHERBOARD" && userSelection.parts["MOTHERBOARD"] != partId) {
        delete userSelection.parts["CPU"]; delete userSelection.meta["CPU"];
        delete userSelection.parts["RAM"]; delete userSelection.meta["RAM"];
        document.getElementById('summary-CPU').innerText = 'Not Selected';
        document.getElementById('summary-RAM').innerText = 'Not Selected';
    }

    userSelection.parts[category] = partId;
    userSelection.meta[category] = componentObj;

    updateSummaryDOM();
    calculateRunningTotalPrice();
    renderCurrentStep();
}

function calculateRunningTotalPrice() {
    let sum = 0;
    Object.keys(userSelection.meta).forEach(catKey => {
        const part = userSelection.meta[catKey];
        if (catKey === "RAM") {
            sum += (part.price * userSelection.ram_quantity);
        } else {
            sum += part.price;
        }
    });
    document.getElementById('liveTotalPrice').innerText = `₱${sum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    return sum;
}

function showFinalSaveState() {
    document.getElementById('ramQtyContainer').style.display = 'none';
    document.getElementById('currentCategoryTitle').innerText = "🏁 Review Plan Matrix";
    document.getElementById('partsCatalog').innerHTML = `
        <div class="col-12 text-center py-4">
            <h4 class="text-success fw-bold mb-3">${editModeData ? 'System Blueprint Configuration Adjusted!' : 'System Blueprint Fully Assembled!'}</h4>
            <p class="text-muted">All parameters structural clearance verified.</p>
        </div>
    `;
    document.getElementById('saveSection').style.display = 'block';
    document.getElementById('nextBtn').disabled = true;
}

async function commitBuildToDatabase() {
    const nameInput = document.getElementById('buildNameInput').value.trim();
    if(!nameInput) return;

    const totalPrice = calculateRunningTotalPrice();
    const payload = {
        build_name: nameInput,
        total_price: totalPrice,
        parts: userSelection.parts,
        ram_quantity: userSelection.ram_quantity, // Isama sa JSON data package patungong backend
        edit_id: editModeData ? editModeData.edit_id : 0 
    };

    try {
        const response = await fetch('custom-builder.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if(result.status === 'success') {
            window.location.href = 'saved-builds.php';
        } else {
            console.error("SQL Error reported by PHP backend:", result.message);
            alert("Error saving: " + result.message);
        }
    } catch (err) {
        console.error("Network Fetch error:", err);
    }
}

window.onload = initBuilder;
</script>
</body>
</html>
