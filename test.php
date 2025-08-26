<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Image Color Picker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-purple-500 via-indigo-500 to-blue-500 p-6">
  <div class="bg-white rounded-2xl shadow-xl p-6 max-w-4xl w-full">
    <h1 class="text-2xl font-bold text-center mb-4">🎨 Image Color Picker</h1>
    
    <!-- Upload -->
    <input type="file" id="upload" accept="image/*" class="mb-4 block mx-auto">

    <!-- Image Preview -->
    <div class="relative flex justify-center">
      <canvas id="canvas" class="rounded-xl shadow-lg cursor-crosshair"></canvas>
      <!-- Tooltip -->
      <div id="tooltip" 
           class="absolute hidden px-2 py-1 text-white text-sm rounded bg-gray-900 shadow-lg">
      </div>
    </div>

    <!-- Selected Color Info -->
    <div class="mt-6 flex items-center justify-center space-x-4">
      <div id="colorBox" class="w-12 h-12 rounded border shadow"></div>
      <div class="flex items-center space-x-2">
        <span id="hexCode" class="font-mono text-lg">#------</span>
        <button id="copyBtn" 
                class="px-3 py-1 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
          Copy
        </button>
      </div>
    </div>
  </div>

  <script>
    const upload = document.getElementById("upload");
    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    const tooltip = document.getElementById("tooltip");
    const colorBox = document.getElementById("colorBox");
    const hexCode = document.getElementById("hexCode");
    const copyBtn = document.getElementById("copyBtn");

    let img = new Image();

    // Upload Image
    upload.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(event) {
        img.onload = function() {
          canvas.width = img.width;
          canvas.height = img.height;
          ctx.drawImage(img, 0, 0);
        }
        img.src = event.target.result;
      }
      reader.readAsDataURL(file);
    });

    // Get Color under cursor
    canvas.addEventListener("mousemove", (e) => {
      const rect = canvas.getBoundingClientRect();
      const x = Math.floor(e.clientX - rect.left);
      const y = Math.floor(e.clientY - rect.top);

      const pixel = ctx.getImageData(x, y, 1, 1).data;
      const hex = rgbToHex(pixel[0], pixel[1], pixel[2]);

      tooltip.textContent = hex;
      tooltip.style.left = `${e.clientX - rect.left + 15}px`;
      tooltip.style.top = `${e.clientY - rect.top + 15}px`;
      tooltip.classList.remove("hidden");

      colorBox.style.backgroundColor = hex;
      hexCode.textContent = hex;
    });

    // Hide tooltip when leaving canvas
    canvas.addEventListener("mouseleave", () => {
      tooltip.classList.add("hidden");
    });

    // Copy to clipboard
    copyBtn.addEventListener("click", () => {
      const color = hexCode.textContent;
      navigator.clipboard.writeText(color).then(() => {
        copyBtn.textContent = "Copied!";
        setTimeout(() => copyBtn.textContent = "Copy", 1200);
      });
    });

    function rgbToHex(r, g, b) {
      return "#" + [r, g, b].map(x => {
        const hex = x.toString(16).padStart(2, "0");
        return hex;
      }).join("");
    }
  </script>
</body>
</html>
