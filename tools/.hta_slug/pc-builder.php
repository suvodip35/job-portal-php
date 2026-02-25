<?php
    $toolName = "PC Builder Online – Build Your Custom Computer Step by Step";
    $toolDesc = "Free online PC builder tool to create custom computer builds with compatibility checking. Perfect for gamers, students, and professionals. Get Amazon & Flipkart affiliate links for components.";

    $pageTitle       = $toolName . " - FromCampus";
    $pageDescription = mb_substr(strip_tags($toolDesc), 0, 160);
    $metaTitle       = $toolName;

    $keywords = "pc builder, computer builder, custom pc, gaming pc, pc parts, computer components, 
    pc compatibility, amazon affiliate, flipkart affiliate, build pc online, FromCampus tools";

    $ogImage      = "https://fromcampus.com/assets/tools-image/pc-builder.jpg";
    $canonicalUrl = "https://fromcampus.com/tools/pc-builder";

    $schema = [
        "@context" => "https://schema.org",
        "@type" => "WebApplication",
        "name" => $toolName,
        "url" => $canonicalUrl,
        "image" => $ogImage,
        "operatingSystem" => "All",
        "applicationCategory" => "UtilityApplication",
        "description" => $toolDesc,
        "creator" => [
            "@type" => "Organization",
            "name" => "FromCampus"
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => "0",
            "priceCurrency" => "INR"
        ]
    ];

    require_once __DIR__ . '/../../.hta_slug/_header.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-4">
                PC Builder 
            </h1>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Build your perfect PC with our step-by-step guide. All components are checked for compatibility and include Amazon & Flipkart affiliate links.
            </p>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Builder Steps -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Step Indicator -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-6 overflow-x-auto" id="stepIndicator">
                        <?php
                        $steps = [
                            ['id' => 'cpu', 'label' => 'CPU', 'icon' => '💻'],
                            ['id' => 'motherboard', 'label' => 'Motherboard', 'icon' => '🔌'],
                            ['id' => 'ram', 'label' => 'RAM', 'icon' => '🧠'],
                            ['id' => 'gpu', 'label' => 'GPU', 'icon' => '🎮'],
                            ['id' => 'storage', 'label' => 'Storage', 'icon' => '💾'],
                            ['id' => 'psu', 'label' => 'PSU', 'icon' => '⚡'],
                            ['id' => 'case', 'label' => 'Case', 'icon' => '📦'],
                            ['id' => 'summary', 'label' => 'Summary', 'icon' => '📋']
                        ];
                        
                        foreach ($steps as $index => $step) {
                            $isActive = $index === 0;
                            ?>
                            <div class="flex items-center flex-shrink-0 step-indicator" data-step="<?= $index ?>">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold step-icon 
                                        <?= $isActive ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white shadow-lg' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' ?>"
                                        data-step="<?= $index ?>">
                                        <?= $step['icon'] ?>
                                    </div>
                                    <span class="text-xs mt-1 font-medium step-label <?= $isActive ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500' ?>"
                                          data-step="<?= $index ?>">
                                        <?= $step['label'] ?>
                                    </span>
                                </div>
                                <?php if ($index < count($steps) - 1): ?>
                                    <div class="w-8 h-1 mx-2 bg-gray-200 dark:bg-gray-700 rounded step-connector" data-step="<?= $index ?>"></div>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Current Step Content -->
                    <div id="stepContent">
                        <!-- Content will be loaded by JavaScript -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between">
                    <button id="prevBtn" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors opacity-0">
                        Previous
                    </button>
                    <button id="nextBtn" class="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-lg font-medium hover:from-pink-600 hover:to-purple-700 transition-all transform hover:scale-105">
                        Next Step
                    </button>
                </div>
            </div>

            <!-- Right Column - Build Summary & Visualizer -->
            <div class="space-y-6">
                <!-- PC Visualizer -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>🖥️</span>
                        Build Visualizer
                    </h3>
                    <div id="pcVisualizer" class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg relative overflow-hidden">
                        <!-- PC visualization will be rendered here by JavaScript -->
                    </div>
                </div>

                <!-- Build Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>🛒</span>
                        Your Build
                    </h3>
                    
                    <div class="space-y-3 mb-4">
                        <?php foreach (['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case'] as $component): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-sm font-medium capitalize"><?= $component ?>:</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 build-component" id="build-<?= $component ?>">
                                    Not selected
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t dark:border-gray-600 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold">Total Estimated Cost:</span>
                            <span class="font-bold text-green-600 text-xl" id="total-price">₹0</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
                            <span>Estimated Wattage:</span>
                            <span id="total-watts">0W</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button id="saveBuild" class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium hover:from-green-600 hover:to-emerald-700 transition-all">
                        💾 Save Build
                    </button>
                    <button id="resetBuild" class="w-full py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg font-medium hover:from-red-600 hover:to-pink-700 transition-all">
                        🔄 Reset Build
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Component Database and JavaScript -->
<script>
// Updated Component Database with Affiliate Links
const COMPONENT_DB = {
    cpu: [
        { 
            id: 'c1', 
            name: 'AMD Ryzen 5 5500', 
            price: 8500, 
            socket: 'AM4', 
            ramType: 'DDR4', 
            watts: 65, 
            integratedGpu: false, 
            tier: 'budget',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'c2', 
            name: 'Intel Core i5-12400F', 
            price: 9500, 
            socket: 'LGA1700', 
            ramType: 'BOTH', 
            watts: 65, 
            integratedGpu: false, 
            tier: 'budget',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'c3', 
            name: 'AMD Ryzen 5 7600', 
            price: 18500, 
            socket: 'AM5', 
            ramType: 'DDR5', 
            watts: 65, 
            integratedGpu: true, 
            tier: 'mid',
            affiliateLinks: {
                amazon: '8'
            }
        },
        { 
            id: 'c4', 
            name: 'Intel Core i5-14400F', 
            price: 21000, 
            socket: 'LGA1700', 
            ramType: 'BOTH', 
            watts: 65, 
            integratedGpu: false, 
            tier: 'mid',
            affiliateLinks: {
                amazon: '3'
            }
        },
        { 
            id: 'c5', 
            name: 'AMD Ryzen 7 9800X3D', 
            price: 42000, 
            socket: 'AM5', 
            ramType: 'DDR5', 
            watts: 120, 
            integratedGpu: true, 
            tier: 'high',
            affiliateLinks: {
                amazon: '7'
            }
        },
        { 
            id: 'c6', 
            name: 'Intel Core Ultra 7 265K', 
            price: 38000, 
            socket: 'LGA1851', 
            ramType: 'DDR5', 
            watts: 125, 
            integratedGpu: true, 
            tier: 'high',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
    ],
    motherboard: [
        { 
            id: 'm1', 
            name: 'Gigabyte A520M K V2', 
            price: 4500, 
            socket: 'AM4', 
            ramType: 'DDR4', 
            size: 'mATX', 
            wifi: false,
            affiliateLinks: {
                amazon: 'Y'
            }
        },
        { 
            id: 'm2', 
            name: 'MSI PRO B760M-E', 
            price: 9800, 
            socket: 'LGA1700', 
            ramType: 'DDR4', 
            size: 'mATX', 
            wifi: false,
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'm3', 
            name: 'ASRock B650M PG Lightning', 
            price: 13500, 
            socket: 'AM5', 
            ramType: 'DDR5', 
            size: 'mATX', 
            wifi: true,
            affiliateLinks: {
                amazon: 'J'
            }
        },
        { 
            id: 'm4', 
            name: 'ASUS TUF Gaming B760-PLUS', 
            price: 18000, 
            socket: 'LGA1700', 
            ramType: 'DDR5', 
            size: 'ATX', 
            wifi: true,
            affiliateLinks: {
                amazon: 'V'
            }
        },
        { 
            id: 'm5', 
            name: 'MSI MAG X870 Tomahawk', 
            price: 28000, 
            socket: 'AM5', 
            ramType: 'DDR5', 
            size: 'ATX', 
            wifi: true,
            affiliateLinks: {
                amazon: '8'
            }
        },
        { 
            id: 'm6', 
            name: 'ASUS ROG Strix Z890-A', 
            price: 35000, 
            socket: 'LGA1851', 
            ramType: 'DDR5', 
            size: 'ATX', 
            wifi: true,
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
    ],
    ram: [
        { 
            id: 'r1', 
            name: 'XPG ADATA GAMMIX D30 16GB (8x2) DDR4 3200MHz', 
            price: 3400, 
            type: 'DDR4', 
            capacity: 16,
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'r2', 
            name: 'Corsair Vengeance LPX 32GB (16x2) DDR4 3600MHz', 
            price: 7200, 
            type: 'DDR4', 
            capacity: 32,
            affiliateLinks: {
                amazon: 'C'
            }
        },
        { 
            id: 'r3', 
            name: 'G.Skill Ripjaws S5 32GB (16x2) DDR5 6000MHz', 
            price: 10500, 
            type: 'DDR5', 
            capacity: 32,
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'r4', 
            name: 'Corsair Dominator Platinum 64GB (32x2) DDR5 6400MHz', 
            price: 24000, 
            type: 'DDR5', 
            capacity: 64,
            affiliateLinks: {
                amazon: 'W'
            }
        },
    ],
    gpu: [
        { 
            id: 'g1', 
            name: 'ASRock Radeon RX 6600 8GB', 
            price: 19500, 
            watts: 132, 
            tier: 'budget',
            affiliateLinks: {
                amazon: '6'
            }
        },
        { 
            id: 'g2', 
            name: 'NVIDIA GeForce RTX 3060 12GB', 
            price: 24500, 
            watts: 170, 
            tier: 'budget',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'g3', 
            name: 'NVIDIA GeForce RTX 4060 8GB', 
            price: 28000, 
            watts: 115, 
            tier: 'mid',
            affiliateLinks: {
                amazon: '6'
            }
        },
        { 
            id: 'g4', 
            name: 'AMD Radeon RX 7700 XT 12GB', 
            price: 41000, 
            watts: 245, 
            tier: 'mid',
            affiliateLinks: {
                amazon: 'J'
            }
        },
        { 
            id: 'g5', 
            name: 'NVIDIA GeForce RTX 5070 12GB', 
            price: 62000, 
            watts: 220, 
            tier: 'high',
            affiliateLinks: {
                amazon: '9'
            }
        },
        { 
            id: 'g6', 
            name: 'NVIDIA GeForce RTX 5090 24GB', 
            price: 185000, 
            watts: 450, 
            tier: 'high',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
    ],
    storage: [
        { 
            id: 's1', 
            name: 'WD Green SN350 500GB NVMe', 
            price: 3200, 
            type: 'NVMe Gen3',
            affiliateLinks: {
                amazon: 'F'
            }
        },
        { 
            id: 's2', 
            name: 'Crucial P3 1TB NVMe', 
            price: 5600, 
            type: 'NVMe Gen3',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 's3', 
            name: 'Samsung 990 PRO 1TB NVMe', 
            price: 9500, 
            type: 'NVMe Gen4',
            affiliateLinks: {
                amazon: 'G'
            }
        },
        { 
            id: 's4', 
            name: 'WD Black SN850X 2TB NVMe', 
            price: 16500, 
            type: 'NVMe Gen4',
            affiliateLinks: {
                amazon: '6'
            }
        },
    ],
    psu: [
        { 
            id: 'p1', 
            name: 'Deepcool PK550D 550W Bronze', 
            price: 3800, 
            watts: 550, 
            modular: false,
            affiliateLinks: {
                amazon: 'C'
            }
        },
        { 
            id: 'p2', 
            name: 'Corsair CV650 650W Bronze', 
            price: 5200, 
            watts: 650, 
            modular: false,
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'p3', 
            name: 'MSI MAG A750GL 750W Gold', 
            price: 8500, 
            watts: 750, 
            modular: true,
            affiliateLinks: {
                amazon: '4'
            }
        },
        { 
            id: 'p4', 
            name: 'Corsair RM1000e 1000W Gold', 
            price: 15500, 
            watts: 1000, 
            modular: true,
            affiliateLinks: {
                amazon: '4'
            }
        },
    ],
    case: [
        { 
            id: 'ca1', 
            name: 'Ant Esports ICE-110 Mid Tower', 
            price: 3200, 
            size: 'ATX', 
            type: 'budget',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
        { 
            id: 'ca2', 
            name: 'Galax Revolution-05 Mesh', 
            price: 4500, 
            size: 'ATX', 
            type: 'budget',
            affiliateLinks: {
                amazon: 'T'
            }
        },
        { 
            id: 'ca3', 
            name: 'Lian Li Lancool 216', 
            price: 8900, 
            size: 'ATX', 
            type: 'mid',
            affiliateLinks: {
                amazon: '4'
            }
        },
        { 
            id: 'ca4', 
            name: 'NZXT H9 Flow Dual-Chamber', 
            price: 16000, 
            size: 'ATX', 
            type: 'high',
            affiliateLinks: {
                amazon: '',
                flipkart: ''
            }
        },
    ]
};

// Application State
let currentBuild = {
    cpu: null,
    motherboard: null,
    ram: null,
    gpu: null,
    storage: null,
    psu: null,
    case: null
};

let currentStep = 0;
const steps = ['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case', 'summary'];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    loadSavedBuild();
    renderStep(currentStep);
    updateBuildSummary();
    updateStepIndicator();
    setupEventListeners();
    renderPCVisualizer();
});

function setupEventListeners() {
    document.getElementById('nextBtn').addEventListener('click', nextStep);
    document.getElementById('prevBtn').addEventListener('click', prevStep);
    document.getElementById('saveBuild').addEventListener('click', saveBuild);
    document.getElementById('resetBuild').addEventListener('click', resetBuild);
}

function updateStepIndicator() {
    // Reset all indicators
    document.querySelectorAll('.step-icon').forEach(icon => {
        icon.classList.remove('bg-gradient-to-r', 'from-pink-500', 'to-purple-600', 'text-white', 'shadow-lg');
        icon.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-500');
    });
    
    document.querySelectorAll('.step-label').forEach(label => {
        label.classList.remove('text-purple-600', 'dark:text-purple-400');
        label.classList.add('text-gray-500');
    });
    
    document.querySelectorAll('.step-connector').forEach(connector => {
        connector.classList.remove('bg-gradient-to-r', 'from-pink-500', 'to-purple-600');
        connector.classList.add('bg-gray-200', 'dark:bg-gray-700');
    });

    // Set current step as active
    const currentStepIcon = document.querySelector(`.step-icon[data-step="${currentStep}"]`);
    const currentStepLabel = document.querySelector(`.step-label[data-step="${currentStep}"]`);
    
    if (currentStepIcon) {
        currentStepIcon.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-500');
        currentStepIcon.classList.add('bg-gradient-to-r', 'from-pink-500', 'to-purple-600', 'text-white', 'shadow-lg');
    }
    
    if (currentStepLabel) {
        currentStepLabel.classList.remove('text-gray-500');
        currentStepLabel.classList.add('text-purple-600', 'dark:text-purple-400');
    }

    // Set previous connectors as active
    for (let i = 0; i < currentStep; i++) {
        const connector = document.querySelector(`.step-connector[data-step="${i}"]`);
        if (connector) {
            connector.classList.remove('bg-gray-200', 'dark:bg-gray-700');
            connector.classList.add('bg-gradient-to-r', 'from-pink-500', 'to-purple-600');
        }
    }
}

function renderStep(stepIndex) {
    const step = steps[stepIndex];
    const stepContent = document.getElementById('stepContent');
    
    if (step === 'summary') {
        renderSummaryStep();
        return;
    }

    const products = COMPONENT_DB[step];
    let html = `
        <div id="step-${step}" class="step-content active">
            <h2 class="text-2xl font-bold mb-4 flex items-center gap-3">
                <span class="text-2xl">${getStepIcon(step)}</span>
                Select ${getStepTitle(step)}
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                ${getStepDescription(step)}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    `;

    products.forEach(product => {
        const isSelected = currentBuild[step]?.id === product.id;
        const isCompatible = checkCompatibility(step, product);
        
        html += `
            <div class="component-card border-2 rounded-xl p-4 cursor-pointer transition-all ${
                isSelected 
                    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20 shadow-lg scale-105' 
                    : isCompatible 
                        ? 'border-gray-200 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md' 
                        : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 opacity-60 cursor-not-allowed'
            }" style="${isSelected ? 'border: 1px solid #7c3aed !important;' : ''}"
            onclick="${isCompatible ? `selectComponent('${step}', '${product.id}')` : ''}">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-lg">${product.name}</h3>
                    ${!isCompatible ? '<span class="text-red-500 text-sm">⚠️ Incompatible</span>' : ''}
                </div>
                
                <div class="space-y-2 mb-3">
                    ${Object.entries(product).map(([key, value]) => {
                        if (['id', 'name', 'price', 'affiliateLinks'].includes(key)) return '';
                        if (typeof value === 'boolean') return value ? `<span class="badge">${key}</span>` : '';
                        return `<span class="badge">${key}: ${value}</span>`;
                    }).join('')}
                </div>
                
                <div class="flex justify-between items-center mt-4">
                    <span class="text-2xl font-bold text-green-600">₹${product.price.toLocaleString('en-IN')}</span>
                    ${isCompatible ? renderAffiliateButtons(product.affiliateLinks) : ''}
                </div>
                
                ${!isCompatible ? `
                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                        ${getIncompatibilityReason(step, product)}
                    </div>
                ` : ''}
            </div>
        `;
    });

    html += `</div></div>`;
    stepContent.innerHTML = html;
    
    updateNavigationButtons();
    updateStepIndicator();
}

function renderAffiliateButtons(affiliateLinks) {
    if (!affiliateLinks) return '';
    
    let buttons = '';
    if (affiliateLinks.amazon) {
        buttons += `
            <a href="${affiliateLinks.amazon}" 
               target="_blank" 
               class="text-xs bg-amber-500 text-white px-3 py-1 rounded-lg hover:bg-amber-600 transition-colors mr-2">
                Amazon
            </a>
        `;
    }
    if (affiliateLinks.flipkart) {
        buttons += `
            <a href="${affiliateLinks.flipkart}" 
               target="_blank" 
               class="text-xs bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition-colors">
                Flipkart
            </a>
        `;
    }
    return `<div class="flex">${buttons}</div>`;
}

function renderSummaryStep() {
    const totalPrice = calculateTotalPrice();
    const totalWatts = calculateTotalWatts();
    
    let html = `
        <div id="step-summary" class="step-content active">
            <h2 class="text-2xl font-bold mb-4 flex items-center gap-3">
                <span class="text-2xl">📋</span>
                Build Summary
            </h2>
            
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <h3 class="font-bold text-green-800 dark:text-green-400">Build Complete!</h3>
                        <p class="text-green-700 dark:text-green-300 text-sm">All components are compatible and ready to build.</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="font-bold text-lg">Components</h3>
                    ${Object.entries(currentBuild).map(([type, component]) => `
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold capitalize">${type}:</span>
                                <span class="text-green-600 font-bold">₹${component?.price?.toLocaleString('en-IN') || '0'}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${component?.name || 'Not selected'}</p>
                            ${component ? renderAffiliateButtons(component.affiliateLinks) : ''}
                        </div>
                    `).join('')}
                </div>
                
                <div class="space-y-4">
                    <h3 class="font-bold text-lg">Build Details</h3>
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Total Cost:</span>
                                <span class="font-bold text-2xl text-green-600">₹${totalPrice.toLocaleString('en-IN')}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Estimated Wattage:</span>
                                <span class="font-bold text-lg">${totalWatts}W</span>
                            </div>
                            <div class="flex justify-between">
                                <span>PSU Headroom:</span>
                                <span class="font-bold ${currentBuild.psu && currentBuild.psu.watts > totalWatts ? 'text-green-600' : 'text-red-600'}">
                                    ${currentBuild.psu ? `${currentBuild.psu.watts - totalWatts}W` : 'N/A'}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Compatibility Check</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-green-600">
                                <span>✅</span>
                                <span>All components are compatible</span>
                            </div>
                            <div class="flex items-center gap-2 text-green-600">
                                <span>✅</span>
                                <span>Power supply is sufficient</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('stepContent').innerHTML = html;
    updateNavigationButtons();
    updateStepIndicator();
}

function renderPCVisualizer() {
    const visualizer = document.getElementById('pcVisualizer');
    const components = [
        { type: 'case', element: currentBuild.case, color: 'bg-gray-400', position: 'inset-0', text: 'CASE' },
        { type: 'motherboard', element: currentBuild.motherboard, color: 'bg-green-500', position: 'w-4/5 h-4/5 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2', text: 'MOBO' },
        { type: 'cpu', element: currentBuild.cpu, color: 'bg-blue-500', position: 'w-1/6 h-1/6 top-1/3 left-1/2 transform -translate-x-1/2 -translate-y-1/2', text: 'CPU' },
        { type: 'ram', element: currentBuild.ram, color: 'bg-yellow-500', position: 'w-1/12 h-1/3 top-1/4 right-1/4', text: 'RAM' },
        { type: 'gpu', element: currentBuild.gpu, color: 'bg-purple-500', position: 'w-2/3 h-1/8 bottom-1/3 left-1/2 transform -translate-x-1/2', text: 'GPU' },
        { type: 'storage', element: currentBuild.storage, color: 'bg-red-500', position: 'w-1/8 h-1/12 bottom-1/4 left-1/3', text: 'SSD' },
        { type: 'psu', element: currentBuild.psu, color: 'bg-orange-500', position: 'w-1/4 h-1/6 bottom-4 left-4', text: 'PSU' },
    ];

    let html = '';
    
    if (!currentBuild.case && !currentBuild.motherboard) {
        html = `
            <div class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400">
                <div class="text-center">
                    <div class="text-4xl mb-2">💻</div>
                    <p class="text-sm">Select components to see visualization</p>
                </div>
            </div>
        `;
    } else {
        components.forEach(comp => {
            if (comp.element) {
                html += `
                    <div class="absolute ${comp.position} ${comp.color} rounded flex items-center justify-center text-white text-xs font-bold opacity-90 transition-all duration-500">
                        ${comp.text}
                    </div>
                `;
            }
        });
    }

    visualizer.innerHTML = html;
}

// Helper Functions
function getStepIcon(step) {
    const icons = {
        cpu: '💻',
        motherboard: '🔌',
        ram: '🧠',
        gpu: '🎮',
        storage: '💾',
        psu: '⚡',
        case: '📦',
        summary: '📋'
    };
    return icons[step] || '📝';
}

function getStepTitle(step) {
    const titles = {
        cpu: 'Processor (CPU)',
        motherboard: 'Motherboard',
        ram: 'Memory (RAM)',
        gpu: 'Graphics Card (GPU)',
        storage: 'Storage',
        psu: 'Power Supply (PSU)',
        case: 'Case'
    };
    return titles[step] || step;
}

function getStepDescription(step) {
    const descriptions = {
        cpu: 'The brain of your computer. Choose AMD Ryzen or Intel Core processors.',
        motherboard: 'Connects all components together. Must match your CPU socket.',
        ram: 'System memory. DDR5 for latest builds, DDR4 for budget options.',
        gpu: 'For gaming and graphics work. The most important for gamers.',
        storage: 'Where your files live. NVMe SSDs are recommended.',
        psu: 'Power supply unit. Choose with adequate wattage headroom.',
        case: 'The housing for your components. Ensure proper size compatibility.'
    };
    return descriptions[step] || 'Select your component.';
}

function checkCompatibility(step, product) {
    switch(step) {
        case 'motherboard':
            return currentBuild.cpu ? product.socket === currentBuild.cpu.socket : true;
        case 'ram':
            if (currentBuild.motherboard) {
                return product.type === currentBuild.motherboard.ramType;
            }
            if (currentBuild.cpu && currentBuild.cpu.ramType !== 'BOTH') {
                return product.type === currentBuild.cpu.ramType;
            }
            return true;
        case 'psu':
            const estimatedWattage = calculateTotalWatts();
            return product.watts >= estimatedWattage;
        default:
            return true;
    }
}

function getIncompatibilityReason(step, product) {
    switch(step) {
        case 'motherboard':
            return `Socket mismatch. Your CPU requires ${currentBuild.cpu?.socket} socket.`;
        case 'ram':
            if (currentBuild.motherboard) {
                return `RAM type mismatch. Motherboard supports ${currentBuild.motherboard.ramType} only.`;
            }
            return `RAM type mismatch. CPU supports ${currentBuild.cpu?.ramType} only.`;
        case 'psu':
            const estimatedWattage = calculateTotalWatts();
            return `Insufficient wattage. Minimum ${estimatedWattage}W required.`;
        default:
            return 'Incompatible with current build.';
    }
}

function selectComponent(type, productId) {
    const product = COMPONENT_DB[type].find(p => p.id === productId);
    currentBuild[type] = product;
    
    // Auto-clear incompatible components
    if (type === 'cpu' && currentBuild.motherboard && currentBuild.motherboard.socket !== product.socket) {
        currentBuild.motherboard = null;
        currentBuild.ram = null;
    }
    if (type === 'motherboard' && currentBuild.ram && currentBuild.ram.type !== product.ramType) {
        currentBuild.ram = null;
    }
    
    updateBuildSummary();
    renderStep(currentStep);
    renderPCVisualizer();
}

function calculateTotalPrice() {
    return Object.values(currentBuild).reduce((total, component) => total + (component?.price || 0), 0);
}

function calculateTotalWatts() {
    return (currentBuild.cpu?.watts || 0) + (currentBuild.gpu?.watts || 0) + 100;
}

function updateBuildSummary() {
    Object.keys(currentBuild).forEach(type => {
        const element = document.getElementById(`build-${type}`);
        if (element) {
            element.textContent = currentBuild[type]?.name || 'Not selected';
            element.className = `text-sm ${currentBuild[type] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400'}`;
        }
    });
    
    document.getElementById('total-price').textContent = `₹${calculateTotalPrice().toLocaleString('en-IN')}`;
    document.getElementById('total-watts').textContent = `${calculateTotalWatts()}W`;
}

function nextStep() {
    if (currentStep < steps.length - 1) {
        currentStep++;
        renderStep(currentStep);
    }
}

function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        renderStep(currentStep);
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    prevBtn.style.opacity = currentStep > 0 ? '1' : '0';
    
    if (currentStep === steps.length - 1) {
        nextBtn.textContent = 'Finish';
        nextBtn.style.display = 'none';
    } else {
        nextBtn.textContent = 'Next Step';
        nextBtn.style.display = 'block';
        
        // Disable next button if current step component not selected
        const currentStepType = steps[currentStep];
        if (currentStepType !== 'summary' && !currentBuild[currentStepType]) {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

function saveBuild() {
    localStorage.setItem('pcBuild', JSON.stringify(currentBuild));
    alert('Build saved successfully! You can continue where you left off.');
}

function loadSavedBuild() {
    const saved = localStorage.getItem('pcBuild');
    if (saved) {
        currentBuild = JSON.parse(saved);
    }
}

function resetBuild() {
    if (confirm('Are you sure you want to reset your build? This cannot be undone.')) {
        currentBuild = {
            cpu: null,
            motherboard: null,
            ram: null,
            gpu: null,
            storage: null,
            psu: null,
            case: null
        };
        currentStep = 0;
        localStorage.removeItem('pcBuild');
        renderStep(currentStep);
        updateBuildSummary();
        renderPCVisualizer();
        updateStepIndicator();
    }
}
</script>

<style>
.badge {
    @apply bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-1 rounded-md font-mono;
}

.component-card {
    transition: all 0.3s ease;
}

.step-content {
    display: none;
}

.step-content.active {
    display: block;
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
    @apply bg-gray-400 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-500 dark:bg-gray-500;
}
</style>

<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        PC Builder – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        The PC Builder tool helps you create custom computer builds with automatic compatibility checking. 
        It's designed for students, gamers, and professionals who want to build their perfect PC without 
        worrying about component compatibility issues.
    </p>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Automatic compatibility checking between components</li>
        <li>Real-time price calculation and wattage estimation</li>
        <li>Amazon & Flipkart affiliate links for easy purchasing</li>
        <li>Interactive PC visualizer showing selected components</li>
        <li>Save and resume your builds anytime</li>
        <li>Responsive design works on all devices</li>
        <li>Dark/light theme based on browser preferences</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Why Use This Tool?</h3>
    <p class="text-gray-700 dark:text-gray-300">
        Building a PC can be overwhelming with countless components and compatibility requirements. 
        This tool simplifies the process by guiding you step-by-step and ensuring all selected 
        components work together perfectly. Perfect for first-time builders and experienced enthusiasts alike.
    </p>
    
    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Perfect For</h3>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Gaming PC builds with optimal performance</li>
        <li>College projects and programming workstations</li>
        <li>Content creation and video editing setups</li>
        <li>Office and productivity computers</li>
        <li>Budget-friendly builds for students</li>
        <li>High-end workstation configurations</li>
    </ul>
</div>