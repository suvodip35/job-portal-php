</main>
<style>
  .roller-counter {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .digit-wrapper {
        overflow: hidden;
        height: 28px; /* adjust as needed */
        display: inline-block;
        width: 20px;
    }

    .digit {
        display: flex;
        flex-direction: column;
        transition: transform 1s ease-in-out;
    }

    .digit div {
        height: 28px; 
        font-size: 22px;
        color: #4dd2ff;
        text-shadow: 0 0 6px rgba(77, 210, 255, 0.4);
        font-weight: bold;
        text-align: center;
    }

    /* Hover effect */
    .roller-counter:hover {
        transform: scale(1.07);
        transition: all 0.2s;
        text-shadow: 0 0 10px rgba(77, 210, 255, 0.9);
    }
</style>
<!-- Fixed Bottom Navigation for Mobile -->
<div class="md:hidden fixed bottom-0 left-0 right-0 w-full max-w-full bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50" style="padding-bottom: env(safe-area-inset-bottom, 0px); box-sizing: border-box; overflow-x: hidden;">
  <div class="flex justify-around items-center w-full max-w-full">
    <a href="/" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
      </svg>
      <span>Home</span>
    </a>
    <a href="/books" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
      </svg>
      <span>Books</span>
    </a>
    <a href="/updates" id="bottomUpdatesLink" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400 bg-gray-700 rounded-full h-fit shadow-xl">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
      </svg>
      <span>Updates</span>
    </a>
    <a href="/tools" id="bottomToolsLink" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
        <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
        </svg>
        <span>Tools</span>
    </a>
    <a href="/saved-jobs" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z" clip-rule="evenodd"></path>
      </svg>
      <span>Saved</span>
    </a>
  </div>
</div>
<footer class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 mt-12" style="content-visibility: auto; contain-intrinsic-size: 1px 300px;">
  <div class="max-w-6xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8 text-sm">

      <!-- Company Info -->
      <div>
        <a href="/" class="text-xl font-semibold flex flex-col justify-center items-center" >
          <img src="/assets/logo/fc_logo_crop.webp" alt="FromCampus Logo" width="40" height="40" loading="lazy" decoding="async" class="w-[40px] h-[40px]" />
          <p class="text-lg">FromCampus</p>
        </a>
        <p class="text-gray-600 dark:text-gray-400">FromCampus is your trusted portal for latest job notifications, mock tests, and career updates.</p>
      </div>

      <!-- Quick Links -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Quick Links</h2>
        <ul class="space-y-2">
          <li><a href="/" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a></li>
          <li><a href="/jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Jobs</a></li>
          <li><a href="/current-affairs" class="hover:text-blue-600 dark:hover:text-blue-400">Current Affairs</a></li>
          <li><a href="/updates" class="hover:text-blue-600 dark:hover:text-blue-400">Updates</a></li>
          <li><a href="/saved-jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Saved Jobs</a></li>
          <li><a href="/contact" class="hover:text-blue-600 dark:hover:text-blue-400">Contact Us</a></li>
        </ul>
      </div>

      <!-- Legal Links -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Legal</h2>
        <ul class="space-y-2">
          <li><a href="/about-us" class="hover:text-blue-600 dark:hover:text-blue-400">About Us</a></li>
          <li><a href="/privacy-policy" class="hover:text-blue-600 dark:hover:text-blue-400">Privacy Policy</a></li>
          <li><a href="/terms" class="hover:text-blue-600 dark:hover:text-blue-400">Terms & Conditions</a></li>
          <li><a href="/disclaimer" class="hover:text-blue-600 dark:hover:text-blue-400">Disclaimer & DMCA</a></li>
          <li><a href="/review" class="hover:text-blue-600 dark:hover:text-blue-400">Review</a></li>
        </ul>
      </div>

      <!-- Social Media -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Follow Us</h2>
        <div class="flex space-x-4">
          <a href="https://www.facebook.com/fromcampus/" target="_blank" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400" aria-label="Visit our Facebook page">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.99H7.898v-2.888h2.54V9.845c0-2.506 1.493-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.875h2.773l-.443 2.888h-2.33v6.99C18.343 21.128 22 16.991 22 12z"/></svg>
          </a>
        </div>
      </div>
      <div>
        <h2 class="">Total Visits:</h2>
        <div class="text-white fw-normal roller-counter" data-count="<?php echo $count ?? 0; ?>" style="min-height: 28px; min-width: 100px; display: inline-flex; align-items: center;">
                <span id="rollerCounter" style="display: inline-flex; min-height: 28px;"></span>
            </div>
      </div>
    </div>
  </div>

  <div class="border-t border-gray-200 dark:border-gray-700 py-4 pb-20 md:pb-4">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-center items-center text-xs text-gray-500 dark:text-gray-400">
      <div>&copy; <?= date('Y') ?> FromCampus. All rights reserved.</div>
      <!-- <div>Built with <span class="text-red-500">♥</span> PHP • Tailwind • SEO Friendly</div> -->
    </div>
  </div>
</footer>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const container = document.getElementById("rollerCounter");
        const target = document.querySelector(".roller-counter").getAttribute("data-count");

        const digits = target.split("");

        digits.forEach(num => {
            const wrapper = document.createElement("div");
            wrapper.className = "digit-wrapper";

            const digitColumn = document.createElement("div");
            digitColumn.className = "digit";

            // 0 to 9 digits for rolling animation
            for (let i = 0; i <= 9; i++) {
                const d = document.createElement("div");
                d.textContent = i;
                digitColumn.appendChild(d);
            }

            wrapper.appendChild(digitColumn);
            container.appendChild(wrapper);

            // Animate to final number
            setTimeout(() => {
                digitColumn.style.transform = `translateY(-${num * 28}px)`; 
            }, 100);
        });

    });
</script>

<!-- <script src="<?= BASE_URL ?>assets/script.js"></script> -->
</body>
</html>
