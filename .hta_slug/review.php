<?php
$pageTitle = "User Reviews | FromCampus";
$pageDescription = "Read real user feedback and share your experience with FromCampus.";
$keywords = "User reviews, feedback, testimonials, FromCampus experience";
$author = "FromCampus";
$ogImage = "https://fromcampus.com/assets/logo/FromCampus_Color_text.png";
$canonicalUrl = "https://fromcampus.com/review";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "Review",
    "name" => "FromCampus User Reviews",
    "url" => "https://fromcampus.com/review",
    "description" => "Users share feedback about FromCampus job alert platform.",
];

require_once "_header.php";

$err = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check($_POST['csrf_token'] ?? '');

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $rating = (int)($_POST["rating"] ?? 0);
    $review = trim($_POST["review"] ?? "");

    $errors = [];

    if ($name === "") $errors[] = "Name is required.";
    if ($rating < 1 || $rating > 5) $errors[] = "Please select a valid rating.";
    if ($review === "") $errors[] = "Review is required.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, review) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $rating, $review]);

            $success = "Thank you! Your review is submitted and will appear after approval.";
            $name = $email = $review = "";
        } catch (Exception $e) {
            $err = "Something went wrong. Please try again.";
        }
    } else {
        $err = implode("<br>", $errors);
    }
}

// Fetch approved reviews
$reviews = $pdo->query("SELECT * FROM reviews WHERE approved = 1 ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating and rating distribution
$totalReviews = count($reviews);
$averageRating = 0;
$ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if ($totalReviews > 0) {
    $totalRating = 0;
    foreach ($reviews as $r) {
        $totalRating += $r['rating'];
        $ratingDistribution[$r['rating']]++;
    }
    $averageRating = round($totalRating / $totalReviews, 1);
}
?>

<main class="max-w-6xl mx-auto px-4 py-10">
    <!-- Header Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4 text-gray-900 dark:text-white">User Reviews</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Discover what our users are saying about FromCampus. Share your own experience to help others.
        </p>
        
        <!-- Write Review Button -->
        <button id="writeReviewBtn" class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center mx-auto">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Write a Review
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Stats -->
        <div class="lg:col-span-1">
            <!-- Rating Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 sticky top-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Rating Summary</h2>
                
                <div class="flex items-center mb-6">
                    <div class="mr-4">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white"><?= $averageRating ?></div>
                        <div class="flex gap-0.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="text-2xl <?= $i <= round($averageRating) ? 'text-yellow-500' : 'text-gray-300 dark:text-gray-600' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Based on <?= $totalReviews ?> reviews</div>
                    </div>
                </div>
                
                <!-- Rating Distribution -->
                <div class="space-y-2">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="flex items-center">
                            <span class="text-sm w-10 text-gray-900 dark:text-white"><?= $i ?> ★</span>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 flex-1 mx-2 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: <?= $totalReviews > 0 ? ($ratingDistribution[$i] / $totalReviews) * 100 : 0 ?>%"></div>
                            </div>
                            <span class="text-sm w-10 text-right text-gray-900 dark:text-white"><?= $ratingDistribution[$i] ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Reviews -->
        <div class="lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Reviews (<?= $totalReviews ?>)</h2>
                
                <?php if ($totalReviews > 0): ?>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-900 dark:text-white">Sort by:</span>
                    <select class="border border-gray-300 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option>Newest First</option>
                        <option>Highest Rated</option>
                        <option>Lowest Rated</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($reviews): ?>
                <div class="space-y-6">
                    <?php foreach ($reviews as $index => $r): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 <?= $index < 2 ? 'border-l-4 border-l-indigo-500 bg-indigo-50 dark:bg-indigo-900/10' : '' ?>">
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xl mr-4">
                                    <?= strtoupper(substr($r['name'], 0, 1)) ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?= htmlspecialchars($r['name']) ?></h3>
                                            <div class="flex gap-0.5 mt-1">
                                                <?= str_repeat("⭐", $r['rating']) ?>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400 mt-1 sm:mt-0">
                                            <?= date("F j, Y", strtotime($r['created_at'])) ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 mt-3 leading-relaxed"><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center border border-gray-200 dark:border-gray-700">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No reviews yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Be the first to share your experience with FromCampus!</p>
                    <button id="writeReviewFirstBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        Write First Review
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0 border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Write a Review</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <?php if ($err): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg"><?= $err ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg"><?= $success ?></div>
                <?php endif; ?>

                <div>
                    <label class="block mb-2 font-medium text-gray-900 dark:text-white">Your Name*</label>
                    <input type="text" name="name" value="<?= e($name ?? '') ?>" 
                           class="w-full border border-gray-300 dark:border-gray-600 p-3 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                           required>
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-900 dark:text-white">Email (Optional)</label>
                    <input type="email" name="email" value="<?= e($email ?? '') ?>" 
                           class="w-full border border-gray-300 dark:border-gray-600 p-3 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-900 dark:text-white">Rating*</label>
                    <div class="flex gap-1 mb-2" id="ratingSelector">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="text-4xl text-gray-300 dark:text-gray-600 cursor-pointer transition-colors duration-200 hover:text-yellow-400" data-rating="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="selectedRating" value="<?= e($rating ?? 0) ?>" required>
                    <div class="text-sm text-gray-500 dark:text-gray-400" id="ratingText">Select your rating</div>
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-900 dark:text-white">Your Review*</label>
                    <textarea name="review" rows="5" 
                              class="w-full border border-gray-300 dark:border-gray-600 p-3 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                              placeholder="Share your experience with FromCampus..." 
                              required><?= e($review ?? '') ?></textarea>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" id="cancelReview" class="flex-1 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('reviewModal');
        const modalContent = modal.querySelector('.bg-white');
        const writeReviewBtn = document.getElementById('writeReviewBtn');
        const writeReviewFirstBtn = document.getElementById('writeReviewFirstBtn');
        const closeModal = document.getElementById('closeModal');
        const cancelReview = document.getElementById('cancelReview');
        
        // Open modal
        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
        
        // Close modal
        function closeModalFunc() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
            document.body.style.overflow = 'auto';
        }
        
        // Event listeners
        if (writeReviewBtn) writeReviewBtn.addEventListener('click', openModal);
        if (writeReviewFirstBtn) writeReviewFirstBtn.addEventListener('click', openModal);
        if (closeModal) closeModal.addEventListener('click', closeModalFunc);
        if (cancelReview) cancelReview.addEventListener('click', closeModalFunc);
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalFunc();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalFunc();
            }
        });

        // Interactive rating selector
        const stars = document.querySelectorAll('#ratingSelector .text-4xl');
        const selectedRating = document.getElementById('selectedRating');
        const ratingText = document.getElementById('ratingText');
        
        const ratingLabels = {
            1: "Poor",
            2: "Fair",
            3: "Average",
            4: "Good",
            5: "Excellent"
        };
        
        function updateStars(rating) {
            stars.forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.classList.remove('text-gray-300', 'dark:text-gray-600');
                    star.classList.add('text-yellow-500');
                } else {
                    star.classList.remove('text-yellow-500');
                    star.classList.add('text-gray-300', 'dark:text-gray-600');
                }
            });
        }
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                selectedRating.value = rating;
                ratingText.textContent = ratingLabels[rating];
                updateStars(rating);
            });
            
            // Add hover effect
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                updateStars(rating);
            });
            
            star.addEventListener('mouseout', function() {
                const currentRating = parseInt(selectedRating.value);
                updateStars(currentRating);
            });
        });
        
        // Initialize with any existing value
        if (selectedRating.value > 0) {
            const rating = parseInt(selectedRating.value);
            updateStars(rating);
            ratingText.textContent = ratingLabels[rating];
        }
    });
</script>

<?php require_once "_footer.php"; ?>