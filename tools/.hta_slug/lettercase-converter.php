<?php
    $toolName = "Letter Case Converter Online – Change Text Case Instantly";
    $toolDesc = "Free online letter case converter tool to change text between uppercase, lowercase, sentence case, title case and more. Perfect for formatting documents, titles, and text content.";

    $pageTitle       = $toolName . " - FromCampus";
    $pageDescription = mb_substr(strip_tags($toolDesc), 0, 160);
    $metaTitle       = $toolName;

    $keywords = "text case converter, uppercase to lowercase, sentence case, title case, capitalize text, 
    online text formatter, case converter, text tools, letter case, FromCampus tools";

    $ogImage      = "https://fromcampus.com/assets/tools-image/letter-case-converter.jpg";
    $canonicalUrl = "https://fromcampus.com/tools/letter-case-converter";

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
            Letter Case Converter
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Input and Controls -->
            <div class="space-y-6">
                <!-- Input Text Area -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        Input Text:
                    </label>
                    <textarea 
                        id="inputText" 
                        rows="8"
                        placeholder="Enter your text here to convert its case..."
                        class="w-full p-4 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                               resize-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    ></textarea>
                </div>
                
                <!-- Case Options -->
                <div>
                    <label class="block mb-3 font-medium text-gray-700 dark:text-gray-300">
                        Convert To:
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            data-case="upper"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out active-case">
                            UPPERCASE
                        </button>
                        <button 
                            data-case="lower"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            lowercase
                        </button>
                        <button 
                            data-case="sentence"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            Sentence case
                        </button>
                        <button 
                            data-case="title"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            Title Case
                        </button>
                        <button 
                            data-case="camel"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            camelCase
                        </button>
                        <button 
                            data-case="pascal"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            PascalCase
                        </button>
                        <button 
                            data-case="snake"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            snake_case
                        </button>
                        <button 
                            data-case="kebab"
                            class="case-option bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 
                                   text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-medium
                                   hover:border-pink-500 hover:text-pink-600 dark:hover:text-pink-400
                                   transition-all duration-200 ease-in-out">
                            kebab-case
                        </button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-4 pt-4">
                    <button 
                        id="copyBtn"
                        class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-400 hover:to-gray-500 
                               text-white font-bold py-3 px-6 rounded-full transition-all duration-200 ease-in-out
                               flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Copy Text</span>
                    </button>
                    <button 
                        id="clearBtn"
                        class="bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-400 hover:to-pink-500 
                               text-white font-bold py-3 px-6 rounded-full transition-all duration-200 ease-in-out
                               flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>
            
            <!-- Right Column - Output -->
            <div class="space-y-6">
                <div>
                    <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">
                        Converted Text:
                    </label>
                    <div 
                        id="outputText"
                        class="w-full min-h-[200px] p-4 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white
                               whitespace-pre-wrap break-words">
                        <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>Your converted text will appear here</p>
                        </div>
                    </div>
                </div>
                
                <!-- Text Statistics -->
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3">Text Statistics</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-pink-600" id="charCount">0</div>
                            <div class="text-gray-600 dark:text-gray-400">Characters</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600" id="wordCount">0</div>
                            <div class="text-gray-600 dark:text-gray-400">Words</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="lineCount">0</div>
                            <div class="text-gray-600 dark:text-gray-400">Lines</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="sentenceCount">0</div>
                            <div class="text-gray-600 dark:text-gray-400">Sentences</div>
                        </div>
                    </div>
                </div>
                
                <!-- Download Option -->
                <button 
                    id="downloadBtn"
                    class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-400 hover:to-purple-500 
                           text-white font-bold py-3 px-6 rounded-full transition-all duration-200 ease-in-out
                           flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Download as Text File</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        Letter Case Converter – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        The Letter Case Converter tool helps you quickly change text between different case formats. 
        Whether you're formatting documents, writing code, preparing content, or working with text data, 
        this tool makes case conversion effortless and accurate.
    </p>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li><strong>UPPERCASE</strong> - Converts all letters to capital letters</li>
        <li><strong>lowercase</strong> - Converts all letters to small letters</li>
        <li><strong>Sentence case</strong> - Capitalizes the first letter of each sentence</li>
        <li><strong>Title Case</strong> - Capitalizes the first letter of each word</li>
        <li><strong>camelCase</strong> - Removes spaces and capitalizes first letter of each word except the first</li>
        <li><strong>PascalCase</strong> - Removes spaces and capitalizes first letter of each word</li>
        <li><strong>snake_case</strong> - Replaces spaces with underscores and uses lowercase</li>
        <li><strong>kebab-case</strong> - Replaces spaces with hyphens and uses lowercase</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Why Use This Tool?</h3>
    <p class="text-gray-700 dark:text-gray-300">
        Proper text formatting is essential for professional documents, programming, content creation, and data processing. 
        This tool saves time and ensures consistency when working with different text case requirements across various platforms and applications.
    </p>
    
    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Common Use Cases</h3>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Formatting titles and headings for documents and websites</li>
        <li>Preparing text for programming variables and function names</li>
        <li>Standardizing data in spreadsheets and databases</li>
        <li>Creating consistent content for social media and marketing</li>
        <li>Formatting academic papers and research documents</li>
        <li>Preparing text for API requests and data exchange</li>
        <li>Converting user input to standardized formats</li>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const inputText = document.getElementById('inputText');
        const outputText = document.getElementById('outputText');
        const caseOptions = document.querySelectorAll('.case-option');
        const copyBtn = document.getElementById('copyBtn');
        const clearBtn = document.getElementById('clearBtn');
        const downloadBtn = document.getElementById('downloadBtn');
        
        // Statistics elements
        const charCount = document.getElementById('charCount');
        const wordCount = document.getElementById('wordCount');
        const lineCount = document.getElementById('lineCount');
        const sentenceCount = document.getElementById('sentenceCount');
        
        let currentCase = 'upper';
        
        // Initialize with sample text
        // const sampleText = "This is a sample text to demonstrate the letter case converter tool. You can type or paste your own text here to convert it to different cases.";
        // inputText.value = sampleText;
        convertCase();
        
        // Event Listeners
        inputText.addEventListener('input', convertCase);
        
        caseOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                caseOptions.forEach(opt => opt.classList.remove('active-case'));
                // Add active class to clicked option
                this.classList.add('active-case');
                // Update current case
                currentCase = this.dataset.case;
                // Convert text
                convertCase();
            });
        });
        
        copyBtn.addEventListener('click', copyToClipboard);
        clearBtn.addEventListener('click', clearAll);
        downloadBtn.addEventListener('click', downloadText);
        
        // Case Conversion Functions
        function convertCase() {
            const text = inputText.value;
            let convertedText = '';
            
            switch(currentCase) {
                case 'upper':
                    convertedText = text.toUpperCase();
                    break;
                case 'lower':
                    convertedText = text.toLowerCase();
                    break;
                case 'sentence':
                    convertedText = toSentenceCase(text);
                    break;
                case 'title':
                    convertedText = toTitleCase(text);
                    break;
                case 'camel':
                    convertedText = toCamelCase(text);
                    break;
                case 'pascal':
                    convertedText = toPascalCase(text);
                    break;
                case 'snake':
                    convertedText = toSnakeCase(text);
                    break;
                case 'kebab':
                    convertedText = toKebabCase(text);
                    break;
            }
            
            // Update output
            if (text.trim()) {
                outputText.innerHTML = convertedText || '<div class="text-center text-gray-500 dark:text-gray-400 py-12">No text to convert</div>';
            } else {
                outputText.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-12">Your converted text will appear here</div>';
            }
            
            // Update statistics
            updateStatistics(text);
        }
        
        function toSentenceCase(text) {
            return text.toLowerCase().replace(/(^\s*|[.!?]\s+)([a-z])/g, (match, p1, p2) => p1 + p2.toUpperCase());
        }
        
        function toTitleCase(text) {
            const smallWords = /^(a|an|and|as|at|but|by|en|for|if|in|of|on|or|the|to|v[.]?|via|vs[.]?)$/i;
            
            return text.toLowerCase().replace(/([^\W_]+[^\s-]*)/g, (match, index, title) => {
                if (index > 0 && index + match.length !== title.length &&
                    match.search(smallWords) > -1 && title.charAt(index - 2) !== ":" &&
                    (title.charAt(index + match.length) !== '-' || title.charAt(index - 1) === '-') &&
                    title.charAt(index - 1).search(/[^\s-]/) < 0) {
                    return match.toLowerCase();
                }
                
                if (match.substr(1).search(/[A-Z]|\../) > -1) {
                    return match;
                }
                
                return match.charAt(0).toUpperCase() + match.substr(1);
            });
        }
        
        function toCamelCase(text) {
            return text.toLowerCase()
                .replace(/[^a-zA-Z0-9]+(.)/g, (match, chr) => chr.toUpperCase())
                .replace(/[^a-zA-Z0-9]/g, '');
        }
        
        function toPascalCase(text) {
            const camel = toCamelCase(text);
            return camel.charAt(0).toUpperCase() + camel.slice(1);
        }
        
        function toSnakeCase(text) {
            return text.toLowerCase()
                .replace(/[^a-zA-Z0-9]+/g, '_')
                .replace(/(^_|_$)/g, '');
        }
        
        function toKebabCase(text) {
            return text.toLowerCase()
                .replace(/[^a-zA-Z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        }
        
        function updateStatistics(text) {
            // Character count
            charCount.textContent = text.length;
            
            // Word count
            const words = text.trim() ? text.trim().split(/\s+/).filter(word => word.length > 0) : [];
            wordCount.textContent = words.length;
            
            // Line count
            const lines = text.trim() ? text.split('\n').filter(line => line.trim().length > 0) : [];
            lineCount.textContent = lines.length;
            
            // Sentence count (approximate)
            const sentences = text.trim() ? text.split(/[.!?]+/).filter(sentence => sentence.trim().length > 0) : [];
            sentenceCount.textContent = sentences.length;
        }
        
        async function copyToClipboard() {
            const text = outputText.innerText;
            
            if (!text || text.includes('Your converted text will appear here')) {
                showNotification('No text to copy!', 'error');
                return;
            }
            
            try {
                await navigator.clipboard.writeText(text);
                showNotification('Text copied to clipboard!', 'success');
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showNotification('Text copied to clipboard!', 'success');
            }
        }
        
        function clearAll() {
            inputText.value = '';
            outputText.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-12">Your converted text will appear here</div>';
            updateStatistics('');
            showNotification('All text cleared!', 'info');
        }
        
        function downloadText() {
            const text = outputText.innerText;
            
            if (!text || text.includes('Your converted text will appear here')) {
                showNotification('No text to download!', 'error');
                return;
            }
            
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `converted-text-${currentCase}-case.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showNotification('Text downloaded successfully!', 'success');
        }
        
        function showNotification(message, type) {
            // Remove existing notification
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white font-medium z-50 transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('transform');
                notification.classList.add('translate-x-0');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('opacity-0', 'transform', '-translate-y-2');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    });
</script>

<style>
    .active-case {
        border-color: #ec4899 !important;
        color: #ec4899 !important;
        background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(168, 85, 247, 0.1)) !important;
    }
    
    .notification {
        transform: translateX(100%);
    }
</style>