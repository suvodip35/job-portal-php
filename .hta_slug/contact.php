<?php require_once "_header.php"; ?>
<main class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold mb-6">Contact Us</h1>
  <form class="space-y-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <div>
      <label class="block text-sm font-medium mb-2">Your Name</label>
      <input type="text" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" required>
    </div>
    <div>
      <label class="block text-sm font-medium mb-2">Email Address</label>
      <input type="email" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" required>
    </div>
    <div>
      <label class="block text-sm font-medium mb-2">Message</label>
      <textarea rows="5" class="w-full px-4 py-2 rounded-md border dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring focus:ring-indigo-500" required></textarea>
    </div>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md">Send</button>
  </form>
</main>
<?php require_once "_footer.php"; ?>
