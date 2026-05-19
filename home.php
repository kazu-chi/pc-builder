<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Center - Synthesis PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: #ffffff; min-height: 100vh; font-family: 'Inter', 'Segoe UI', sans-serif; }
        .navbar { background: #1e1e1e; border-bottom: 1px solid #333; position: relative; z-index: 10; }
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
        .pc-text { color: #add8e6; }
        
        .hero-section {
            position: relative;
            padding: 60px 0 20px 0;
            margin-bottom: 20px;
            overflow: hidden;
            background-color: #121212;
            background-image: 
                linear-gradient(rgba(13, 110, 253, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(13, 110, 253, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center top;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 400px;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.15) 0%, rgba(173, 216, 230, 0.05) 50%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
            pointer-events: none;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }

        .menu-card { 
            background: rgba(30, 30, 30, 0.75); 
            backdrop-filter: blur(10px); 
            border: 1px solid #444;
            border-radius: 20px;
            transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: #ffffff;
            padding: 50px !important;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .menu-card:hover { 
            border-color: #add8e6; 
            transform: translateY(-10px); 
            background: rgba(37, 37, 37, 0.9);
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.2);
        }

        .text-muted { color: #b3b3b3 !important; }
        .welcome-text { color: #add8e6; text-shadow: 0 0 15px rgba(173, 216, 230, 0.4); }
        .section-title { font-weight: 700; letter-spacing: 1px; color: #ffffff; }

        .build-card {
            background: #1e1e1e; border: none; border-radius: 15px;
            overflow: hidden; transition: 0.3s; cursor: pointer; color: white;
            display: block; width: 100%; text-align: left; padding: 0;
        }
        .build-card:hover { transform: scale(1.02); box-shadow: 0 0 20px rgba(173, 216, 230, 0.15); }
        .build-img { width: 100%; height: 220px; object-fit: cover; background: #2b2b2b; transition: 0.5s; }
        .build-card:hover .build-img { filter: brightness(1.1); }
        .build-info { padding: 25px; border: 1px solid #333; border-top: none; border-radius: 0 0 15px 15px; }
        .price-tag { color: #add8e6; font-weight: 800; font-size: 1.4rem; margin-top: 10px; }

        .modal-content { background: #181818; border: 1px solid #333; border-radius: 20px; color: #eee; }
        .modal-header { border-bottom: 1px solid #2a2a2a; padding: 25px; }
        .modal-body { padding: 30px; }
        .btn-close { filter: invert(1); }
        
        .component-list { list-style: none; padding: 0; }
        .component-list li { 
            display: flex; 
            align-items: flex-start;
            padding: 15px 0; 
            border-bottom: 1px solid #262626; 
        }
        .comp-name { color: #add8e6; font-weight: 700; width: 140px; text-transform: uppercase; font-size: 0.85rem; }
        .comp-desc { flex-grow: 1; color: #ffffff; font-size: 0.95rem; line-height: 1.4; }
        .comp-price { color: #ffffff; font-family: 'Courier New', monospace; font-weight: bold; min-width: 100px; text-align: right; }
        .total-section { border-top: 2px solid #add8e6; padding-top: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3">
    <a href="home.php" class="text-decoration-none">
        <span class="brand-name text-white text-uppercase">Synthesis <span class="pc-text">PC</span></span>
    </a>
    <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin-parts.php" class="btn btn-success btn-sm px-4 rounded-pill me-2 fw-bold" style="box-shadow: 0 0 10px rgba(25, 135, 84, 0.4);">
                ⚙️ Manage Parts
            </a>
        <?php endif; ?>

        <a href="saved-builds.php" class="btn btn-outline-info btn-sm px-4 rounded-pill me-2">Saved Builds</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill">Sign Out</a>
    </div>
</nav>

<div class="hero-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Welcome, <span class="welcome-text"><?php echo htmlspecialchars($username); ?></span></h1>
            <p class="text-muted fs-5 mx-auto" style="max-width: 700px;">
                Build your ideal workstation or performance rig using our configuration tools. 
                Select a pathway below to begin your hardware synthesis.
            </p>
        </div>

        <div class="row justify-content-center g-4 mb-4">
            <div class="col-md-6">
                <a href="ai-builder.php" class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <h2 class="fw-bold mb-3 text-uppercase" style="letter-spacing: 3px;">AI Engine Builder</h2>
                        <p class="text-muted mb-0">Let Our AI build the Best PC Build according to your liking!</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="custom-builder.php" class="text-decoration-none">
                    <div class="card menu-card text-center">
                        <h2 class="fw-bold mb-3 text-uppercase" style="letter-spacing: 3px;">Build Your Own!</h2>
                        <p class="text-muted mb-0">Build your own PC in a very easy way!</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <hr class="border-secondary my-5" style="opacity: 0.2;">

    <h3 class="section-title mb-4 text-uppercase text-center">🔥POPULAR BUILDS🔥</h3>
    <div class="row g-4 pb-5">
        <div class="col-md-4">
            <button class="build-card h-100" data-bs-toggle="modal" data-bs-target="#build1">
                <img src="https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=500" class="build-img">
                <div class="build-info">
                    <h5 class="fw-bold mb-1 text-uppercase">Essential Performance Tier</h5>
                    <p class="text-muted small mb-3">Cost-effective architecture designed for high-efficiency daily tasks and entry-level creative workloads.</p>
                    <div class="price-tag">₱26,500</div>
                </div>
            </button>
        </div>

        <div class="col-md-4">
            <button class="build-card h-100" data-bs-toggle="modal" data-bs-target="#build2">
                <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&q=80&w=500" class="build-img">
                <div class="build-info">
                    <h5 class="fw-bold mb-1 text-uppercase">Professional Creator Series</h5>
                    <p class="text-muted small mb-3">Optimized for multi-threaded processing, high-fidelity streaming, and advanced content production.</p>
                    <div class="price-tag">₱58,400</div>
                </div>
            </button>
        </div>

        <div class="col-md-4">
            <button class="build-card h-100" data-bs-toggle="modal" data-bs-target="#build3">
                <img src="https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&q=80&w=500" class="build-img">
                <div class="build-info">
                    <h5 class="fw-bold mb-1 text-uppercase">Elite Enthusiast Masterpiece</h5>
                    <p class="text-muted small mb-3">Uncompromising hardware integration for 4K rendering, extreme simulation, and peak compute power.</p>
                    <div class="price-tag">₱180,900</div>
                </div>
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="build1" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-uppercase">Essential Performance Tier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">This configuration prioritizes value-per-watt, utilizing high-performance integrated graphics to eliminate the immediate need for a dedicated GPU while maintaining system fluidity.</p>
                <ul class="component-list">
                    <li><span class="comp-name">Processor</span><span class="comp-desc">AMD Ryzen 5 5600G <small class="d-block text-muted">Featuring 6 cores and 12 threads with built-in Radeon Graphics capability.</small></span><span class="comp-price">₱7,200</span></li>
                    <li><span class="comp-name">Mainboard</span><span class="comp-desc">Gigabyte B450M DS3H V2 <small class="d-block text-muted">A stable foundations for AM4 architecture with reliable power delivery.</small></span><span class="comp-price">₱4,500</span></li>
                    <li><span class="comp-name">Memory</span><span class="comp-desc">16GB T-Force Delta RGB 3200MHz <small class="d-block text-muted">Dual-channel configuration optimized for high-bandwidth data processing.</small></span><span class="comp-price">₱2,800</span></li>
                    <li><span class="comp-name">Storage</span><span class="comp-desc">500GB Kingston NV2 NVMe <small class="d-block text-muted">Next-generation Gen4 speeds ensuring rapid boot sequences and file access.</small></span><span class="comp-price">₱2,100</span></li>
                    <li><span class="comp-name">Power Unit</span><span class="comp-desc">Corsair CV550 80+ Bronze <small class="d-block text-muted">Certified efficiency providing consistent power with overhead for future expansion.</small></span><span class="comp-price">₱3,200</span></li>
                    <li><span class="comp-name">Chassis</span><span class="comp-desc">Tecware Forge M2 Mesh <small class="d-block text-muted">High-airflow micro-ATX design featuring three pre-installed ARGB fans.</small></span><span class="comp-price">₱2,500</span></li>
                </ul>
                <div class="text-end total-section mt-4"><h3 class="fw-bold welcome-text">ESTIMATED TOTAL: ₱26,500</h3></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="build2" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-uppercase">Professional Creator Series</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">Engineered for the modern professional, this build balances high-frequency single-core speeds with heavy-duty multi-core rendering capabilities.</p>
                <ul class="component-list">
                    <li><span class="comp-name">Processor</span><span class="comp-desc">Intel Core i5-13400F <small class="d-block text-muted">A versatile 10-core hybrid architecture for seamless multitasking and production.</small></span><span class="comp-price">₱12,500</span></li>
                    <li><span class="comp-name">Graphics</span><span class="comp-desc">ASUS Dual RTX 3060 12GB <small class="d-block text-muted">Exceptional VRAM capacity for high-resolution textures and AI-accelerated workflows.</small></span><span class="comp-price">₱18,500</span></li>
                    <li><span class="comp-name">Mainboard</span><span class="comp-desc">MSI B760M Bomber WiFi <small class="d-block text-muted">Advanced chipset featuring integrated WiFi 6E and robust thermal solutions.</small></span><span class="comp-price">₱7,800</span></li>
                    <li><span class="comp-name">Cooling</span><span class="comp-desc">DeepCool AK400 Air Cooler <small class="d-block text-muted">Highly efficient single-tower heat dissipation for sustained heavy loads.</small></span><span class="comp-price">₱1,500</span></li>
                    <li><span class="comp-name">Power Unit</span><span class="comp-desc">Cooler Master MWE 650W Gold <small class="d-block text-muted">Gold-rated 80+ efficiency ensuring minimal energy loss and low operating noise.</small></span><span class="comp-price">₱4,800</span></li>
                </ul>
                <div class="text-end total-section mt-4"><h3 class="fw-bold welcome-text">ESTIMATED TOTAL: ₱58,400</h3></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="build3" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-uppercase">Elite Enthusiast Masterpiece</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">A high-end system built with premium-grade materials, offering unparalleled compute performance for the most demanding technical environments.</p>
                <ul class="component-list">
                    <li><span class="comp-name">Processor</span><span class="comp-desc">Intel Core i9-14900K <small class="d-block text-muted">Extreme 24-core processing power designed for high-tier enthusiast benchmarks.</small></span><span class="comp-price">₱36,500</span></li>
                    <li><span class="comp-name">Graphics</span><span class="comp-desc">RTX 4080 Super 16GB <small class="d-block text-muted">Flagship-grade Ada Lovelace architecture with advanced Ray Tracing acceleration.</small></span><span class="comp-price">₱65,900</span></li>
                    <li><span class="comp-name">Memory</span><span class="comp-desc">32GB Trident Z5 6000MHz DDR5 <small class="d-block text-muted">Next-generation ultra-low latency DDR5 memory for instantaneous system response.</small></span><span class="comp-price">₱8,500</span></li>
                    <li><span class="comp-name">AIO Liquid</span><span class="comp-desc">NZXT Kraken Elite 360 RGB <small class="d-block text-muted">360mm radiator with a customizable LCD screen for real-time telemetry.</small></span><span class="comp-price">₱12,500</span></li>
                    <li><span class="comp-name">Chassis</span><span class="comp-desc">Lian Li O11 Dynamic EVO <small class="d-block text-muted">Premium dual-chamber design optimized for custom cooling and display-grade builds.</small></span><span class="comp-price">₱9,800</span></li>
                </ul>
                <div class="text-end total-section mt-4"><h3 class="fw-bold welcome-text">ESTIMATED TOTAL: ₱180,900</h3></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
