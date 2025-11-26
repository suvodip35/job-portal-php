<?php
    $toolName = "QR Code Generator Online – Create Custom QR Codes";
    $toolDesc = "Free online QR code generator tool to create custom QR codes with logos, colors, and styles. Generate QR codes for URLs, text, contact info, WiFi, and more. Download in high quality PNG format.";

    $pageTitle       = $toolName . " - FromCampus";
    $pageDescription = mb_substr(strip_tags($toolDesc), 0, 160);
    $metaTitle       = $toolName;

    $keywords = "qr code generator, create qr code, custom qr code, qr code with logo, qr code maker, 
    online qr generator, qr code design, business qr code, wifi qr code, contact qr code, FromCampus tools";

    $ogImage      = "https://fromcampus.com/assets/tools-image/qr-code-generator.jpg";
    $canonicalUrl = "https://fromcampus.com/tools/qr-code-generator";

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
<div class="flex flex-col items-center justify-center transition-all duration-300">
    <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-lg p-8 w-full max-w-4xl transition-all duration-300">
        <h2 class="text-3xl font-semibold mb-6 text-center text-gray-900 dark:text-white">
            QR Code Generator
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column - Controls -->
            <div class="space-y-6">
                <!-- QR Type Selection -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        QR Code Type:
                    </label>
                    <select id="qrType" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="url">URL/Website</option>
                        <option value="text">Plain Text</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone Number</option>
                        <option value="sms">SMS</option>
                        <option value="wifi">WiFi Network</option>
                        <option value="vcard">Contact (vCard)</option>
                    </select>
                </div>
                
                <!-- Content Input -->
                <div id="urlInput" class="qr-input-section">
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        Website URL:
                    </label>
                    <input type="url" id="urlContent" placeholder="https://example.com" 
                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                
                <div id="textInput" class="qr-input-section hidden">
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        Text Content:
                    </label>
                    <textarea id="textContent" placeholder="Enter your text here..." rows="3"
                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                </div>
                
                <div id="emailInput" class="qr-input-section hidden">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Email Address:
                            </label>
                            <input type="email" id="emailAddress" placeholder="user@example.com"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Subject (Optional):
                            </label>
                            <input type="text" id="emailSubject" placeholder="Email subject"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Body (Optional):
                            </label>
                            <textarea id="emailBody" placeholder="Email body content" rows="2"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>
                
                <div id="phoneInput" class="qr-input-section hidden">
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        Phone Number:
                    </label>
                    <input type="tel" id="phoneNumber" placeholder="+1234567890"
                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                
                <div id="smsInput" class="qr-input-section hidden">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Phone Number:
                            </label>
                            <input type="tel" id="smsNumber" placeholder="+1234567890"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Message:
                            </label>
                            <textarea id="smsMessage" placeholder="Your SMS message" rows="2"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>
                
                <div id="wifiInput" class="qr-input-section hidden">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Network Name (SSID):
                            </label>
                            <input type="text" id="wifiSsid" placeholder="MyWiFiNetwork"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Password:
                            </label>
                            <input type="text" id="wifiPassword" placeholder="WiFi password"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Encryption Type:
                            </label>
                            <select id="wifiEncryption" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="WPA">WPA/WPA2</option>
                                <option value="WEP">WEP</option>
                                <option value="nopass">No Encryption</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div id="vcardInput" class="qr-input-section hidden">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                    First Name:
                                </label>
                                <input type="text" id="vcardFirstName" placeholder="John"
                                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                    Last Name:
                                </label>
                                <input type="text" id="vcardLastName" placeholder="Doe"
                                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Organization:
                            </label>
                            <input type="text" id="vcardOrganization" placeholder="Company Name"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Phone:
                            </label>
                            <input type="tel" id="vcardPhone" placeholder="+1234567890"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Email:
                            </label>
                            <input type="email" id="vcardEmail" placeholder="john.doe@example.com"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Website:
                            </label>
                            <input type="url" id="vcardWebsite" placeholder="https://example.com"
                                class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                
                <!-- Design Options -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                    <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-white">Design Options</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                QR Color:
                            </label>
                            <input type="color" id="qrColor" value="#000000" 
                                class="w-full h-10 p-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                                Background Color:
                            </label>
                            <input type="color" id="bgColor" value="#FFFFFF" 
                                class="w-full h-10 p-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                            QR Code Size:
                        </label>
                        <input type="range" id="qrSize" min="100" max="500" step="50" value="300" 
                            class="w-full mb-2 bg-gray-200 dark:bg-gray-700 appearance-none h-2 rounded-full outline-none">
                        <p class="text-center font-medium text-gray-900 dark:text-white">
                            Size: <span id="sizeValue">300px</span>
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="addLogo" class="mr-2">
                            <span class="text-gray-700 dark:text-gray-300">Add Logo to QR Code</span>
                        </label>
                        
                        <div id="logoUploadSection" class="mt-2 hidden">
                            <input type="file" id="logoUpload" accept="image/*" 
                                class="block w-full text-sm text-gray-700 dark:text-gray-300 
                                file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                file:text-sm file:font-semibold 
                                file:bg-gradient-to-r file:from-pink-500 file:to-purple-600
                                file:text-white hover:file:from-pink-400 hover:file:to-purple-500">
                        </div>
                    </div>
                </div>
                
                <!-- Generate Button -->
                <button id="generateBtn" 
                    class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-400 hover:to-purple-500 
                    text-white font-bold py-3 px-4 rounded-full transition-all duration-200 ease-in-out mt-4">
                    Generate QR Code
                </button>
            </div>
            
            <!-- Right Column - Preview -->
            <div class="flex flex-col items-center justify-center">
                <div class="bg-gray-100 dark:bg-gray-700 p-6 rounded-lg w-full max-w-xs h-80 flex items-center justify-center">
                    <div id="qrPreview" class="text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-24 h-24 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <p>Your QR code will appear here</p>
                    </div>
                    <canvas id="qrCanvas" class="hidden max-w-full max-h-full"></canvas>
                </div>
                
                <!-- Download Button -->
                <a id="downloadLink" href="#" download="QR-Code.png"
                    class="w-full justify-center bg-gradient-to-r from-pink-500 to-purple-600
                    hover:from-pink-400 hover:to-purple-500 text-white font-bold py-2 px-4 rounded-full 
                    text-center transition-all duration-200 ease-in-out mt-4 hidden">
                    Download QR Code
                </a>
                
                <!-- QR Code Info -->
                <div id="qrInfo" class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400 hidden">
                    <p>Scan this QR code with your phone's camera</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        QR Code Generator – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        Our free QR Code Generator helps you create custom QR codes for various purposes. 
        Whether you need to share a website, contact information, WiFi credentials, or any other data, 
        this tool makes it easy to generate professional QR codes instantly.
    </p>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Create QR codes for URLs, text, emails, phone numbers, and more</li>
        <li>Customize colors and design to match your brand</li>
        <li>Add your logo to create branded QR codes</li>
        <li>Generate WiFi QR codes for easy network sharing</li>
        <li>Create vCard QR codes to share contact information</li>
        <li>Download high-quality PNG images</li>
        <li>Works 100% in your browser - no data is sent to servers</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Why Use QR Codes?</h3>
    <p class="text-gray-700 dark:text-gray-300">
        QR codes provide a convenient way to share information in the digital age. 
        They're used in marketing materials, business cards, product packaging, event tickets, 
        and many other applications. With our tool, you can create professional QR codes 
        that enhance your digital presence and make information sharing effortless.
    </p>
    
    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Common Use Cases</h3>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Business cards with contact information</li>
        <li>Restaurant menus with online ordering</li>
        <li>Product packaging with additional information</li>
        <li>Event tickets with quick access to details</li>
        <li>WiFi network sharing in offices and cafes</li>
        <li>Marketing materials with website links</li>
        <li>Educational resources with supplementary content</li>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const qrType = document.getElementById('qrType');
        const qrInputSections = document.querySelectorAll('.qr-input-section');
        const generateBtn = document.getElementById('generateBtn');
        const qrPreview = document.getElementById('qrPreview');
        const qrCanvas = document.getElementById('qrCanvas');
        const downloadLink = document.getElementById('downloadLink');
        const qrInfo = document.getElementById('qrInfo');
        const qrSize = document.getElementById('qrSize');
        const sizeValue = document.getElementById('sizeValue');
        const addLogo = document.getElementById('addLogo');
        const logoUploadSection = document.getElementById('logoUploadSection');
        const logoUpload = document.getElementById('logoUpload');
        
        // Show/hide input sections based on QR type
        qrType.addEventListener('change', function() {
            qrInputSections.forEach(section => {
                section.classList.add('hidden');
            });
            
            const selectedType = qrType.value;
            document.getElementById(selectedType + 'Input').classList.remove('hidden');
        });
        
        // Update size value display
        qrSize.addEventListener('input', function() {
            sizeValue.textContent = qrSize.value + 'px';
        });
        
        // Show/hide logo upload section
        addLogo.addEventListener('change', function() {
            if (addLogo.checked) {
                logoUploadSection.classList.remove('hidden');
            } else {
                logoUploadSection.classList.add('hidden');
            }
        });
        
        // Generate QR code
        generateBtn.addEventListener('click', generateQRCode);
        
        // Auto-generate when inputs change
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (element.id !== 'logoUpload') {
                element.addEventListener('input', generateQRCode);
            }
        });
        
        // Initial generation
        generateQRCode();
        
        function generateQRCode() {
            const type = qrType.value;
            let qrData = '';
            
            // Build QR data based on type
            switch(type) {
                case 'url':
                    qrData = document.getElementById('urlContent').value || 'https://fromcampus.com';
                    break;
                case 'text':
                    qrData = document.getElementById('textContent').value || 'Sample text for QR code';
                    break;
                case 'email':
                    const email = document.getElementById('emailAddress').value || 'example@fromcampus.com';
                    const subject = document.getElementById('emailSubject').value;
                    const body = document.getElementById('emailBody').value;
                    qrData = `mailto:${email}`;
                    if (subject) qrData += `?subject=${encodeURIComponent(subject)}`;
                    if (body) qrData += `${subject ? '&' : '?'}body=${encodeURIComponent(body)}`;
                    break;
                case 'phone':
                    const phone = document.getElementById('phoneNumber').value || '+1234567890';
                    qrData = `tel:${phone}`;
                    break;
                case 'sms':
                    const smsNumber = document.getElementById('smsNumber').value || '+1234567890';
                    const smsMessage = document.getElementById('smsMessage').value || 'Hello from FromCampus!';
                    qrData = `sms:${smsNumber}?body=${encodeURIComponent(smsMessage)}`;
                    break;
                case 'wifi':
                    const ssid = document.getElementById('wifiSsid').value || 'MyWiFi';
                    const password = document.getElementById('wifiPassword').value || 'password123';
                    const encryption = document.getElementById('wifiEncryption').value;
                    qrData = `WIFI:S:${ssid};T:${encryption};P:${password};;`;
                    break;
                case 'vcard':
                    const firstName = document.getElementById('vcardFirstName').value || 'John';
                    const lastName = document.getElementById('vcardLastName').value || 'Doe';
                    const organization = document.getElementById('vcardOrganization').value || 'FromCampus';
                    const vcardPhone = document.getElementById('vcardPhone').value || '+1234567890';
                    const vcardEmail = document.getElementById('vcardEmail').value || 'john.doe@fromcampus.com';
                    const vcardWebsite = document.getElementById('vcardWebsite').value || 'https://fromcampus.com';
                    
                    qrData = `BEGIN:VCARD
VERSION:3.0
N:${lastName};${firstName}
FN:${firstName} ${lastName}
ORG:${organization}
TEL:${vcardPhone}
EMAIL:${vcardEmail}
URL:${vcardWebsite}
END:VCARD`;
                    break;
            }
            
            // Generate QR code
            const qr = qrcode(0, 'M');
            qr.addData(qrData);
            qr.make();
            
            // Set canvas size
            const size = parseInt(qrSize.value);
            qrCanvas.width = size;
            qrCanvas.height = size;
            
            const ctx = qrCanvas.getContext('2d');
            
            // Set colors
            const qrColor = document.getElementById('qrColor').value;
            const bgColor = document.getElementById('bgColor').value;
            
            // Draw background
            ctx.fillStyle = bgColor;
            ctx.fillRect(0, 0, size, size);
            
            // Draw QR code
            const moduleCount = qr.getModuleCount();
            const moduleSize = size / moduleCount;
            
            ctx.fillStyle = qrColor;
            for (let row = 0; row < moduleCount; row++) {
                for (let col = 0; col < moduleCount; col++) {
                    if (qr.isDark(row, col)) {
                        ctx.fillRect(
                            col * moduleSize,
                            row * moduleSize,
                            moduleSize,
                            moduleSize
                        );
                    }
                }
            }
            
            // Add logo if selected
            if (addLogo.checked && logoUpload.files.length > 0) {
                const logoFile = logoUpload.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const logo = new Image();
                    logo.onload = function() {
                        // Calculate logo size (20% of QR code size)
                        const logoSize = size * 0.2;
                        const logoX = (size - logoSize) / 2;
                        const logoY = (size - logoSize) / 2;
                        
                        // Draw white background for logo
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(logoX - 2, logoY - 2, logoSize + 4, logoSize + 4);
                        
                        // Draw logo
                        ctx.drawImage(logo, logoX, logoY, logoSize, logoSize);
                        
                        // Update download link
                        updateDownloadLink();
                    };
                    logo.src = e.target.result;
                };
                
                reader.readAsDataURL(logoFile);
            } else {
                updateDownloadLink();
            }
            
            // Show QR code and hide placeholder
            qrPreview.classList.add('hidden');
            qrCanvas.classList.remove('hidden');
            downloadLink.classList.remove('hidden');
            qrInfo.classList.remove('hidden');
        }
        
        function updateDownloadLink() {
            qrCanvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                downloadLink.href = url;
            });
        }
    });
</script>