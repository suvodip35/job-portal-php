<?php
require_once __DIR__ . '/../../.hta_slug/_header.php';
require('../.hta_config/conf.php');

try {
    $db = new PDO("mysql:host=$mariaServer;dbname=$mariaDb", $mariaUser, $mariaPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure the URL parameters work with or without trailing slash
    $uri = $_SERVER['REQUEST_URI'];
    parse_str(parse_url($uri, PHP_URL_QUERY), $queryParams);

    // Default values: current month
    $currentYear = date('Y');
    $currentMonth = date('m');
    $currentMonthStart = date('Y-m-01');
    $currentMonthEnd = date('Y-m-t');

    // Extract query parameters
    $startDate = isset($queryParams['start_date']) ? $queryParams['start_date'] : $currentMonthStart;
    $endDate = isset($queryParams['end_date']) ? $queryParams['end_date'] : $currentMonthEnd;
    $selectedMonth = isset($queryParams['month']) ? $queryParams['month'] : "$currentYear-$currentMonth";

    // If month is selected, override date range
    if (isset($queryParams['month']) && preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
        $startDate = date('Y-m-01', strtotime($selectedMonth));
        $endDate = date('Y-m-t', strtotime($selectedMonth));
    }

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        die("Invalid date format. Please use YYYY-MM-DD.");
    }

    $stmt = $db->prepare(" SELECT e.customerId, c.name, e.emiAmount, e.emiDate, e.payStatus, e.outstanding  FROM emi e  JOIN customers c ON e.customerId = c.customerId  WHERE e.emiDate BETWEEN :startDate AND :endDate  ORDER BY e.emiDate ASC");
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $emiPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total demand amount
    $totalDemand = array_sum(array_column($emiPlans, 'emiAmount'));
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>


<div class="container mt-5">
    <h3 class="mb-4 text-center" style="color: #374151;">Pending EMIs (<?php echo date('F Y', strtotime($startDate)); ?>)</h3>

    <!-- Date & Month Filter Form -->
    <form method="GET" class="d-flex flex-wrap gap-3 align-items-end justify-content-center">
        <div class="">
            <label for="month" class="form-label">Select Month</label>
            <input type="month" id="month" name="month" class="form-control" value="<?php echo htmlspecialchars($selectedMonth); ?>">
        </div>
        <div class="text-center" style="margin-bottom: 7px;">
            <span class="fw-bold">OR</span>
        </div>
        <div class="">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
        </div>
        <div class="">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
        </div>
        <div class="text-center">
            <button type="submit" class="btn text-white px-4" style="background-color: #374151;">Filter</button>
        </div>
    </form>

    <!-- EMI Table -->
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-hover">
            <thead class="text-white text-center" style="background-color: #374151;">
                <tr>
                    <th>Customer Name</th>
                    <th>EMI Amount</th>
                    <th>EMI Date</th>
                    <th>Pay Status</th>
                    <th>Outstanding</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($emiPlans)) : ?>
                    <?php foreach ($emiPlans as $emi) : ?>
                        <tr class="text-center">
                            <td><?php echo htmlspecialchars($emi['name']); ?></td>
                            <td>$<?php echo number_format($emi['emiAmount'], 2); ?></td>
                            <td><?php echo date('m/d/Y', strtotime($emi['emiDate'])); ?></td>
                            <td>
                                <?php if ($emi['payStatus'] == 0) : ?>
                                    <span class="badge bg-danger">Pending</span>
                                <?php else : ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($emi['outstanding'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No pending EMIs in this period</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Total Demand Amount -->
    <div class="alert fw-bold text-center mt-3" style="background-color: #37415130;">
        <h4 class="mb-0">Total Demand EMI Amount: $<?php echo number_format($totalDemand, 2); ?></h4>
    </div>
</div>
