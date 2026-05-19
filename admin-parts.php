<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
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

$message = "";
$msg_type = "";

if (isset($_POST['add_component'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $price = floatval($_POST['price']);
    $socket = $conn->real_escape_string($_POST['socket']);
    $ram_gen = $conn->real_escape_string($_POST['ram_gen']);
    $performance_tag = $conn->real_escape_string($_POST['performance_tag']);
    
    $ram_slots = ($type === 'MOTHERBOARD') ? intval($_POST['ram_slots']) : 0;

    $sql = "INSERT INTO components (name, type, brand, price, socket, ram_gen, performance_tag, ram_slots) 
            VALUES ('$name', '$type', '$brand', $price, '$socket', '$ram_gen', '$performance_tag', $ram_slots)";
    
    if ($conn->query($sql)) {
        $message = "Hardware component successfully deployed to database matrix.";
        $msg_type = "success";
    } else {
        $message = "Insertion Error: " . $conn->error;
        $msg_type = "danger";
    }
}

if (isset($_POST['update_component'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $type = $conn->real_escape_string($_POST['type']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $price = floatval($_POST['price']);
    $socket = $conn->real_escape_string($_POST['socket']);
    $ram_gen = $conn->real_escape_string($_POST['ram_gen']);
    $performance_tag = $conn->real_escape_string($_POST['performance_tag']);
    
    $ram_slots = ($type === 'MOTHERBOARD') ? intval($_POST['ram_slots']) : 0;

    $sql = "UPDATE components SET name='$name', type='$type', brand='$brand', price=$price, 
            socket='$socket', ram_gen='$ram_gen', performance_tag='$performance_tag', ram_slots=$ram_slots WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Component structure metrics successfully updated.";
        $msg_type = "success";
    } else {
        $message = "Update Error: " . $conn->error;
        $msg_type = "danger";
    }
}

// --- 3. ACTION: DELETE COMPONENT ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    $sql = "DELETE FROM components WHERE id = $del_id";
    if ($conn->query($sql)) {
        $message = "Hardware item successfully purged from inventory index.";
        $msg_type = "success";
    } else {
        $message = "Purge Denied: Component may be linked to saved user rigs. -> " . $conn->error;
        $msg_type = "danger";
    }
}

$filter_type = isset($_GET['filter_type']) ? $conn->real_escape_string($_GET['filter_type']) : 'ALL';

if ($filter_type !== 'ALL') {
    $sql_fetch = "SELECT * FROM components WHERE type = '$filter_type' ORDER BY id DESC";
} else {
    $sql_fetch = "SELECT * FROM components ORDER BY id DESC";
}
$result_components = $conn->query($sql_fetch);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #ffffff; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        
        .navbar { background: #1e1e1e; border-bottom: 2px solid #198754; } 
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
        .admin-text { color: #2ec4b6; }
        .panel-card { background: #1e1e1e; border: 1px solid #333; border-radius: 15px; }
        
        .table-dark-custom { background-color: #1e1e1e !important; color: #ffffff !important; border-color: #333333 !important; }
        .table-dark-custom th { color: #2ec4b6 !important; text-transform: uppercase; font-size: 0.85rem; background-color: #1e1e1e !important; }
        .table-dark-custom td { color: #ffffff !important; background-color: #1e1e1e !important; }
        
        .form-control, .form-select { 
            background-color: #ffffff !important; 
            color: #000000 !important; 
            border: 1px solid #198754 !important; 
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus { 
            border-color: #2ec4b6 !important; 
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25) !important; 
            color: #000000 !important;
            background-color: #ffffff !important;
        }
        .form-select option {
            color: #000000 !important;
            background-color: #ffffff !important;
        }

        .modal-content { background: #181818; border: 1px solid #444; color: #ffffff; border-radius: 15px; }
        
        .text-muted {
            color: #b3b3b3 !important;
        }
        
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <span class="brand-name text-white text-uppercase">Synthesis <span class="admin-text">CORE (ADMIN)</span></span>
    <div>
        <a href="home.php" class="btn btn-outline-success btn-sm px-4 rounded-pill me-2">Back to Dashboard</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill">Sign Out</a>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Hardware Matrix Repository</h1>
            <p class="text-muted">Global control framework for adding, tweaking, and pulling out PC architectural parts.</p>
        </div>
        <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addComponentModal">
            + Inject New Component
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="panel-card p-3 mb-4 d-flex align-items-center justify-content-between">
        <form method="GET" action="admin-parts.php" class="row g-2 align-items-center w-100">
            <div class="col-auto">
                <label for="filter_type" class="text-white small fw-bold text-uppercase">Filter Framework:</label>
            </div>
            <div class="col-md-3 col-sm-6">
                <select name="filter_type" id="filter_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="ALL" <?php echo ($filter_type == 'ALL') ? 'selected' : ''; ?>>SHOW ALL CATEGORIES</option>
                    <option value="MOTHERBOARD" <?php echo ($filter_type == 'MOTHERBOARD') ? 'selected' : ''; ?>>MOTHERBOARD</option>
                    <option value="CPU" <?php echo ($filter_type == 'CPU') ? 'selected' : ''; ?>>CPU</option>
                    <option value="RAM" <?php echo ($filter_type == 'RAM') ? 'selected' : ''; ?>>RAM</option>
                    <option value="GPU" <?php echo ($filter_type == 'GPU') ? 'selected' : ''; ?>>GPU</option>
                    <option value="SSD" <?php echo ($filter_type == 'SSD') ? 'selected' : ''; ?>>SSD</option>
                    <option value="PSU" <?php echo ($filter_type == 'PSU') ? 'selected' : ''; ?>>PSU</option>
                    <option value="CASE" <?php echo ($filter_type == 'CASE') ? 'selected' : ''; ?>>CASE</option>
                </select>
            </div>
            <?php if($filter_type !== 'ALL'): ?>
                <div class="col-auto">
                    <a href="admin-parts.php" class="btn btn-sm btn-outline-secondary rounded-pill">Reset Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel-card p-4">
        <div class="table-responsive">
            <table class="table table-dark-custom align-middle m-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Brand & Model Name</th>
                        <th>Est. Price</th>
                        <th>Attributes (Socket/Gen/Slots)</th>
                        <th class="text-center">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_components->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No database entries identified for this filter selection.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($part = $result_components->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-warning small text-uppercase"><?php echo htmlspecialchars($part['type']); ?></td>
                                <td>
                                    <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($part['brand']); ?></span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($part['name']); ?></span>
                                </td>
                                <td>₱<?php echo number_format($part['price'], 2); ?></td>
                                <td class="small text-muted">
                                    <?php 
                                    $attributes = [];
                                    if ($part['socket']) $attributes[] = "Socket: ".$part['socket'];
                                    if ($part['ram_gen']) $attributes[] = "Gen: ".$part['ram_gen'];
                                    // Ipakita lang ang Slots count sa matrix kapag Motherboard
                                    if ($part['type'] === 'MOTHERBOARD') $attributes[] = "Slots: ".$part['ram_slots'];
                                    if ($part['performance_tag']) $attributes[] = $part['performance_tag'];
                                    
                                    echo implode(" | ", $attributes);
                                    ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1" 
                                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($part)); ?>)">
                                        Edit
                                    </button>
                                    <a href="admin-parts.php?delete_id=<?php echo $part['id']; ?>&filter_type=<?php echo $filter_type; ?>" 
                                       class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                       onclick="return confirm('Permanently wipe this component out of database data records?')">
                                         Purge
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addComponentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="admin-parts.php?filter_type=<?php echo $filter_type; ?>">
                <div class="modal-header border-success">
                    <h5 class="modal-title fw-bold text-uppercase text-success">Inject New Hardware Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Component Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ryzen 5 5600X, G.Skill Ripjaws V" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Category Type</label>
                            <select name="type" id="add_type" class="form-select" required>
                                <option value="MOTHERBOARD">MOTHERBOARD</option>
                                <option value="CPU">CPU</option>
                                <option value="RAM">RAM</option>
                                <option value="GPU">GPU</option>
                                <option value="SSD">SSD</option>
                                <option value="PSU">PSU</option>
                                <option value="CASE">CASE</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Brand Manufacturer</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. AMD, ASUS, Corsair" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small text-muted">Valuation Unit Price (₱)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Socket Framework</label>
                            <input type="text" name="socket" class="form-control" placeholder="e.g. AM4, LGA1700">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">RAM Architecture Generation</label>
                            <input type="text" name="ram_gen" class="form-control" placeholder="e.g. DDR4, DDR5">
                        </div>
                        
                        <div class="col-md-4" id="add_ram_slots_container">
                            <label class="form-label small text-muted fw-bold text-success">Motherboard RAM Slots Capacity</label>
                            <select name="ram_slots" class="form-select">
                                <option value="4">4 Slots Standard Matrix</option>
                                <option value="2">2 Slots Compact Matrix</option>
                                <option value="8">8 Slots Workstation Matrix</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="add_performance_tag_container">
                            <label class="form-label small text-muted">Performance Classification Tag</label>
                            <input type="text" name="performance_tag" class="form-control" placeholder="e.g. High-End, Budget">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-success">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_component" class="btn btn-success rounded-pill px-4">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editComponentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="admin-parts.php?filter_type=<?php echo $filter_type; ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-success">
                    <h5 class="modal-title fw-bold text-uppercase text-success">Modify Component Blueprint Metrics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Component Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Category Type</label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="MOTHERBOARD">MOTHERBOARD</option>
                                <option value="CPU">CPU</option>
                                <option value="RAM">RAM</option>
                                <option value="GPU">GPU</option>
                                <option value="SSD">SSD</option>
                                <option value="PSU">PSU</option>
                                <option value="CASE">CASE</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Brand Manufacturer</label>
                            <input type="text" name="brand" id="edit_brand" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small text-muted">Valuation Unit Price (₱)</label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Socket Framework</label>
                            <input type="text" name="socket" id="edit_socket" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">RAM Generation</label>
                            <input type="text" name="ram_gen" id="edit_ram_gen" class="form-control">
                        </div>

                        <div class="col-md-4" id="edit_ram_slots_container">
                            <label class="form-label small text-muted fw-bold text-warning">Motherboard RAM Slots Capacity</label>
                            <select name="ram_slots" id="edit_ram_slots" class="form-select">
                                <option value="4">4 Slots Standard Matrix</option>
                                <option value="2">2 Slots Compact Matrix</option>
                                <option value="8">8 Slots Workstation Matrix</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="edit_performance_tag_container">
                            <label class="form-label small text-muted">Performance Classification Tag</label>
                            <input type="text" name="performance_tag" id="edit_performance_tag" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-success">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_component" class="btn btn-success rounded-pill px-4 fw-bold">Commit Adjustments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleRamSlotsField(typeValue, containerId) {
    const container = document.getElementById(containerId);
    if (typeValue === 'MOTHERBOARD') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

document.getElementById('add_type').addEventListener('change', function() {
    toggleRamSlotsField(this.value, 'add_ram_slots_container');
});

document.getElementById('edit_type').addEventListener('change', function() {
    toggleRamSlotsField(this.value, 'edit_ram_slots_container');
});

function openEditModal(partData) {
    document.getElementById('edit_id').value = partData.id;
    document.getElementById('edit_name').value = partData.name;
    document.getElementById('edit_type').value = partData.type;
    document.getElementById('edit_brand').value = partData.brand;
    document.getElementById('edit_price').value = partData.price;
    document.getElementById('edit_socket').value = partData.socket || '';
    document.getElementById('edit_ram_gen').value = partData.ram_gen || '';
    document.getElementById('edit_performance_tag').value = partData.performance_tag || '';
    
    document.getElementById('edit_ram_slots').value = partData.ram_slots || '4';

    toggleRamSlotsField(partData.type, 'edit_ram_slots_container');

    const editModal = new bootstrap.Modal(document.getElementById('editComponentModal'));
    editModal.show();
}

window.onload = function() {
    toggleRamSlotsField(document.getElementById('add_type').value, 'add_ram_slots_container');
};
</script>
</body>
</html>
