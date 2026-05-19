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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    header('Content-Type: application/json');
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (isset($data['build_id'])) {
        $target_build_id = intval($data['build_id']);
        
        $check_stmt = $conn->prepare("SELECT build_id FROM builds WHERE build_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $target_build_id, $user_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        
        if ($check_res->num_num_rows > 0 || $check_res) { 
            $del_items = $conn->prepare("DELETE FROM build_items WHERE build_id = ?");
            $del_items->bind_param("i", $target_build_id);
            $del_items->execute();
            
            $del_build = $conn->prepare("DELETE FROM builds WHERE build_id = ? AND user_id = ?");
            $del_build->bind_param("ii", $target_build_id, $user_id);
            
            if ($del_build->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Unauthorized operation parameters."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing core identification payload."]);
    }
    exit();
}

$saved_builds = [];

$sql_builds = "SELECT build_id, name, total_price, created_at FROM builds WHERE user_id = $user_id ORDER BY build_id DESC";
$res_builds = $conn->query($sql_builds);

if ($res_builds) {
    while ($build = $res_builds->fetch_assoc()) {
        $b_id = $build['build_id'];
        $components = [];

        $sql_items = "SELECT c.type AS category, c.name AS component_name, c.brand, c.price 
                      FROM build_items bi 
                      INNER JOIN components c ON bi.component_id = c.id 
                      WHERE bi.build_id = $b_id";
        
        $res_items = $conn->query($sql_items);
        if ($res_items) {
            while ($item = $res_items->fetch_assoc()) {
                $components[$item['category']] = [
                    'details' => "[" . $item['brand'] . "] " . $item['component_name'],
                    'price' => (float)$item['price']
                ];
            }
        }

        $saved_builds[] = [
            'id' => $build['build_id'],
            'build_name' => $build['name'],
            'total_price' => $build['total_price'],
            'date_created' => date("M d, Y", strtotime($build['created_at'])),
            'components' => $components
        ];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Builds - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #ffffff; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #1e1e1e; border-bottom: 1px solid #333; }
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
        .pc-text { color: #add8e6; }
        .archive-header { padding: 40px 0 20px 0; background-color: #121212; }
        
        .text-muted { color: #aaaaaa !important; }
        .text-secondary { color: #cccccc !important; }

        .saved-card { background: #1e1e1e; border: 1px solid #333; border-radius: 15px; transition: 0.3s; }
        .saved-card:hover { border-color: #add8e6; transform: translateY(-3px); }
        .price-text { color: #add8e6; font-weight: 700; font-size: 1.25rem; }
        
        .modal-content { background: #181818; border: 1px solid #333; border-radius: 20px; color: #ffffff; }
        .modal-header { border-bottom: 1px solid #2a2a2a; color: #ffffff; }
        .modal-body { color: #ffffff; }
        
        .table-dark-custom { 
            background-color: #1e1e1e !important; 
            color: #ffffff !important; 
            border-color: #333333 !important;
        }
        .table-dark-custom th { 
            color: #add8e6 !important; 
            text-transform: uppercase; 
            font-size: 0.85rem; 
            border-bottom: 2px solid #333333 !important;
            background-color: #1e1e1e !important;
        }
        .table-dark-custom td { 
            color: #ffffff !important; 
            background-color: #1e1e1e !important;
            border-bottom: 1px solid #2a2a2a !important;
        }
        
        .text-warning-custom { color: #ffd166 !important; }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a href="home.php" class="text-decoration-none">
        <span class="brand-name text-white text-uppercase">Synthesis <span class="pc-text">PC</span></span>
    </a>
    <div class="d-flex align-items-center">
        <a href="home.php" class="btn btn-outline-info btn-sm px-4 rounded-pill me-2">Return Home</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill">Sign Out</a>
    </div>
</nav>

<div class="archive-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">Your Saved Configurations</h1>
                <p class="text-muted mb-0">Review and inspect the structural parts database of your builds.</p>
            </div>
            <span class="badge bg-secondary px-3 py-2 rounded-pill fs-6"><?php echo count($saved_builds); ?> Saved Rig(s)</span>
        </div>
        <hr class="border-secondary opacity-25">
    </div>
</div>

<div class="container py-4">
    <?php if (empty($saved_builds)): ?>
        <div class="text-center py-5">
            <h3 class="text-secondary mb-3">No blueprints logged in architecture archive.</h3>
            <a href="custom-builder.php" class="btn btn-info rounded-pill px-4">Start Fabricating</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($saved_builds as $build): ?>
                <div class="col-md-6 col-lg-4" id="card-container-<?php echo $build['id']; ?>">
                    <div class="card saved-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-success bg-opacity-25 text-success rounded-pill py-1 px-2 small">Custom Build</span>
                                <small class="text-muted"><?php echo $build['date_created']; ?></small>
                            </div>
                            <h4 class="fw-bold mb-2 text-white text-truncate"><?php echo htmlspecialchars($build['build_name']); ?></h4>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary">
                            <div>
                                <span class="small d-block text-muted">Total Cost</span>
                                <span class="price-text">₱<?php echo number_format($build['total_price'], 2); ?></span>
                            </div>
                            <button class="btn btn-info btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#buildModal-<?php echo $build['id']; ?>">
                                Inspect Rig
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="buildModal-<?php echo $build['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-uppercase text-white"><?php echo htmlspecialchars($build['build_name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-dark-custom align-middle m-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%;">Component Type</th>
                                                <th style="width: 55%;">Hardware Model Name</th>
                                                <th style="width: 20%;" class="text-end">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($build['components'])): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-warning-custom py-4">
                                                        ⚠️ No hardware data components linked to this system build.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($build['components'] as $type => $part_data): ?>
                                                    <tr>
                                                        <td class="fw-bold text-info text-uppercase small"><?php echo htmlspecialchars($type); ?></td>
                                                        <td class="text-white"><?php echo htmlspecialchars($part_data['details']); ?></td>
                                                        <td class="text-end text-white-50 fw-semibold">
                                                            ₱<?php echo number_format($part_data['price'], 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center border-top border-secondary pt-3 mt-4">
                                    <div>
                                        <a href="custom-builder.php?edit_id=<?php echo $build['id']; ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3 me-2">
                                            Modify Build
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="purgeBuildFromArchive(<?php echo $build['id']; ?>)">
                                            Purge Record
                                        </button>
                                    </div>
                                    <h4 class="fw-bold text-info mb-0">TOTAL VALUATION: ₱<?php echo number_format($build['total_price'], 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function purgeBuildFromArchive(buildId) {
    if (!confirm("Are you absolutely sure you want to permanently delete this system architecture record?")) {
        return;
    }

    try {
        const response = await fetch('saved-builds.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ build_id: buildId })
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            const modalElement = document.getElementById(`buildModal-${buildId}`);
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
            
            const cardContainer = document.getElementById(`card-container-${buildId}`);
            if (cardContainer) {
                cardContainer.remove();
            }
            
            window.location.reload();
        } else {
            alert("Database Error: " + result.message);
        }
    } catch (err) {
        console.error("Network Fetch request error:", err);
        alert("An error occurred while communicating with the storage server.");
    }
}
</script>
</body>
</html>
