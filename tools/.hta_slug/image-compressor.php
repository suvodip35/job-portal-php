<?php
    $toolName = "Image Compressor Online – Reduce Image Size Without Losing Quality";
    $toolDesc = "Free online image compressor tool to reduce photo size in KB without losing quality. Perfect for job applications, government forms, college admissions, and document uploads. Compress JPG, PNG, JPEG instantly.";

    $pageTitle       = $toolName . " - FromCampus";
    $pageDescription = mb_substr(strip_tags($toolDesc), 0, 160);
    $metaTitle       = $toolName;

    $keywords = "image compressor, photo compressor, reduce image size, compress jpg, compress png, image kb reducer, 
    online image compressor, image resizer, passport photo size, signature size reducer, FromCampus tools";

    $ogImage      = "https://fromcampus.com/assets/tools-image/image-compressor.jpg"; // তুমি চাইলে নিজের image path দাও
    $canonicalUrl = "https://fromcampus.com/tools/image-compressor";

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
<div class="flex items-center justify-center transition-all duration-300">

    <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-lg p-8 w-full max-w-xl transition-all duration-300">
        <h2 class="text-3xl font-semibold mb-6 text-center text-gray-900 dark:text-white">
            Image Compressor
        </h2>

        <input 
            type="file" 
            name="imageInput" 
            id="imageInput"
            class="block w-full text-sm text-gray-700 dark:text-gray-300 
            file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
            file:text-sm file:font-semibold 
            file:bg-gradient-to-r file:from-pink-500 file:to-purple-600
            file:text-white hover:file:from-pink-400 hover:file:to-purple-500 
            cursor-pointer mb-4"
            accept="image/*" />

        <label 
            for="qualityInput"
            class="block mb-2 font-medium text-gray-700 dark:text-gray-300 text-center">
            Choose Image Quality:
        </label>

        <input
            id="qualityInput"
            type="range"
            class="w-full mb-4 bg-gray-200 dark:bg-gray-700 appearance-none h-2 rounded-full outline-none"
            min="10"
            max="100"
            step="10"
            value="100">

        <p class="text-center font-bold text-lg text-gray-900 dark:text-white">
            Quality: <span id="qualityValue" class="text-pink-600"></span>
        </p>

        <div class="flex justify-center mb-4">
            <img
                id="preview" 
                src=""
                alt="Compressed Image"
                class="max-w-full h-auto rounded-lg shadow-md"
                style="display: none;">
        </div>

        <p class="text-center font-bold text-lg mb-4 text-gray-900 dark:text-gray-200" id="imageSize">
            Compressed Image Size: 0KB
        </p>

        <a 
            id="downloadLink"
            href="#"
            download="Compressed-Image.jpg"
            class="w-full justify-center bg-gradient-to-r from-pink-500 to-purple-600
            hover:from-pink-400 hover:to-purple-500 text-white font-bold py-2 px-4 rounded-full 
            text-center transition-all duration-200 ease-in-out"
            style="display: none;">
            Download Compressed Image
        </a>
    </div>
</div>
<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        Image Compressor – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        The Image Compressor tool helps you reduce the file size of photos without losing much quality. 
        It is designed for students, job seekers, and professionals who need to upload images for 
        online forms, applications, and documents.
    </p>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Compress images to reduce file size (10KB–500KB).</li>
        <li>Ideal for government job forms, college admissions, and exam portals.</li>
        <li>Maintains good visual quality even after compression.</li>
        <li>Works 100% inside your browser — no image is uploaded to any server.</li>
        <li>Supports JPG, JPEG, PNG and converts to lightweight compressed JPG.</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Why Use This Tool?</h3>
    <p class="text-gray-700 dark:text-gray-300">
        Websites like SSC, PSC, UPSC, Railway, Banking exams, College/University portals require images 
        under a specific size limit. Using this Image Compressor, you can easily prepare your photo 
        and signature within seconds.
    </p>
</div>

<script>
    function compressImage(){
        const fileInput = document.getElementById('imageInput');
        const file = fileInput.files[0];

        file ? document.getElementById('preview').style.display = 'block' : '';

        if(!file){
            alert('Please select an image file.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event){
            const image = new Image();
            image.src = event.target.result;

            image.onload = function(){
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                canvas.width = image.width;
                canvas.height = image.height;

                ctx.drawImage(image, 0, 0);

                const qualityValue = parseFloat(document.getElementById('qualityInput').value) / 100;

                canvas.toBlob(function(blob){
                    const url = URL.createObjectURL(blob);

                    document.getElementById('preview').src = url;

                    const downloadLink = document.getElementById('downloadLink');
                    downloadLink.href = url;
                    downloadLink.style.display = 'flex';

                    const compressedSize = (blob.size / 1024).toFixed(2);
                    document.getElementById('imageSize').textContent =
                        `Compressed Image Size: ${compressedSize} KB`;

                }, 'image/jpeg', qualityValue);
            };
        };

        reader.readAsDataURL(file);
    }

    document.getElementById('qualityInput').addEventListener('input', function(){
        document.getElementById('qualityValue').textContent = this.value + "%";
        compressImage();
    });

    document.getElementById('imageInput').addEventListener('change', compressImage);
</script>
