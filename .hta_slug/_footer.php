</main>
<!-- Fixed Bottom Navigation for Mobile -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50">
  <div class="flex justify-around">
    <a href="<?= BASE_URL ?>" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
      </svg>
      <span>Home</span>
    </a>
    <a href="#" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
      </svg>
      <span>Tests</span>
    </a>
    <a href="/saved-jobs" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z" clip-rule="evenodd"></path>
      </svg>
      <span>Saved</span>
    </a>
    <button id="bottomSubscribePushBtn" class="flex flex-col items-center p-2 text-xs hover:text-blue-600 dark:hover:text-blue-400">
      <svg class="w-5 h-5 mb-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
      </svg>
      <span>Alerts</span>
    </button>
  </div>
</div>
<footer class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 mt-12">
  <div class="max-w-6xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">

      <!-- Company Info -->
      <div>
        <a href="/" class="text-xl font-semibold flex flex-col justify-center items-center" >
          <img src="/assets/logo/fc_logo_crop.png" alt="FromCampus Logo" class="w-[40px] h-auto" />
          <p class="text-lg"><?= e(APP_NAME) ?></p>
        </a>
        <p class="text-gray-600 dark:text-gray-400"><?= e(APP_NAME) ?> is your trusted portal for latest job notifications, mock tests, and career updates.</p>
      </div>

      <!-- Quick Links -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Quick Links</h2>
        <ul class="space-y-2">
          <li><a href="<?= BASE_URL ?>" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a></li>
          <li><a href="<?= BASE_URL ?>jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Jobs</a></li>
          <li><a href="<?= BASE_URL ?>saved-jobs" class="hover:text-blue-600 dark:hover:text-blue-400">Saved Jobs</a></li>
          <li><a href="<?= BASE_URL ?>mock-tests" class="hover:text-blue-600 dark:hover:text-blue-400">Mock Tests</a></li>
          <li><a href="<?= BASE_URL ?>contact" class="hover:text-blue-600 dark:hover:text-blue-400">Contact Us</a></li>
        </ul>
      </div>

      <!-- Legal Links -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Legal</h2>
        <ul class="space-y-2">
          <li><a href="<?= BASE_URL ?>privacy-policy" class="hover:text-blue-600 dark:hover:text-blue-400">Privacy Policy</a></li>
          <li><a href="<?= BASE_URL ?>terms" class="hover:text-blue-600 dark:hover:text-blue-400">Terms & Conditions</a></li>
        </ul>
      </div>

      <!-- Social Media -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Follow Us</h2>
        <div class="flex space-x-4">
          <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400" aria-label="Visit our Facebook page">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.99H7.898v-2.888h2.54V9.845c0-2.506 1.493-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.875h2.773l-.443 2.888h-2.33v6.99C18.343 21.128 22 16.991 22 12z"/></svg>
          </a>
          <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-pink-600 dark:hover:text-pink-400" aria-label="Visit our Instagram page">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.206.056 2.003.24 2.466.402a4.92 4.92 0 0 1 1.77 1.146 4.918 4.918 0 0 1 1.147 1.77c.162.463.346 1.26.402 2.466.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.056 1.206-.24 2.003-.402 2.466a4.918 4.918 0 0 1-1.146 1.77 4.92 4.92 0 0 1-1.77 1.147c-.463.162-1.26.346-2.466.402-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.206-.056-2.003-.24-2.466-.402a4.92 4.92 0 0 1-1.77-1.146 4.918 4.918 0 0 1-1.147-1.77c-.162-.463-.346-1.26-.402-2.466C2.175 15.747 2.163 15.367 2.163 12s.012-3.584.07-4.85c.056-1.206.24-2.003.402-2.466a4.918 4.918 0 0 1 1.146-1.77 4.92 4.92 0 0 1 1.77-1.147c.463-.162 1.26-.346 2.466-.402C8.416 2.175 8.796 2.163 12 2.163zm0 1.687c-3.162 0-3.532.012-4.78.07-.99.046-1.524.21-1.877.35-.473.183-.81.4-1.165.755-.355.355-.572.692-.755 1.165-.14.353-.304.887-.35 1.877-.058 1.248-.07 1.618-.07 4.78s.012 3.532.07 4.78c.046.99.21 1.524.35 1.877.183.473.4.81.755 1.165.355.355.692.572 1.165.755.353.14.887.304 1.877.35 1.248.058 1.618.07 4.78.07s3.532-.012 4.78-.07c.99-.046 1.524-.21 1.877-.35.473-.183.81-.4 1.165-.755.355-.355.572-.692.755-1.165.14-.353.304-.887.35-1.877.058-1.248.07-1.618.07-4.78s-.012-3.532-.07-4.78c-.046-.99-.21-1.524-.35-1.877a3.255 3.255 0 0 0-.755-1.165 3.255 3.255 0 0 0-1.165-.755c-.353-.14-.887-.304-1.877-.35-1.248-.058-1.618-.07-4.78-.07zm0 3.918a5.919 5.919 0 1 1 0 11.837 5.919 5.919 0 0 1 0-11.837zm0 9.772a3.853 3.853 0 1 0 0-7.706 3.853 3.853 0 0 0 0 7.706z"/></svg>
          </a>
          <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-300" aria-label="Visit our Twitter page">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.633 7.997c.013.175.013.35.013.526 0 5.368-4.087 11.556-11.556 11.556-2.3 0-4.435-.675-6.235-1.84.326.038.64.051.979.051a8.186 8.186 0 0 0 5.077-1.748 4.087 4.087 0 0 1-3.817-2.835c.25.038.503.063.765.063.364 0 .727-.05 1.065-.138a4.082 4.082 0 0 1-3.276-4.004v-.05c.54.3 1.16.477 1.82.502a4.082 4.082 0 0 1-1.818-3.401c0-.752.2-1.445.553-2.047a11.6 11.6 0 0 0 8.426 4.27 4.604 4.604 0 0 1-.101-.934 4.082 4.082 0 0 1 4.082-4.082c1.178 0 2.24.502 2.985 1.308a8.07 8.07 0 0 0 2.588-.987 4.075 4.075 0 0 1-1.796 2.25 8.143 8.143 0 0 0 2.353-.64 8.758 8.758 0 0 1-2.037 2.115z"/></svg>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="border-t border-gray-200 dark:border-gray-700 py-4">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-center items-center text-xs text-gray-500 dark:text-gray-400">
      <div>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</div>
      <!-- <div>Built with <span class="text-red-500">♥</span> PHP • Tailwind • SEO Friendly</div> -->
    </div>
  </div>
</footer>


<!-- <script src="<?= BASE_URL ?>assets/script.js"></script> -->
</body>
</html>
