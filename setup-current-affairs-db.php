<?php
/**
 * Current Affairs Database Setup & Sample Data Seeder Script
 */

require_once '.hta_config/config.php';

echo "Setting up Current Affairs database table & sample data...\n\n";

try {
    $sql = "CREATE TABLE IF NOT EXISTS current_affairs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        category VARCHAR(100) DEFAULT 'National',
        description LONGTEXT NOT NULL,
        event_date DATE DEFAULT NULL,
        thumbnail VARCHAR(255) DEFAULT NULL,
        pdf_link VARCHAR(255) DEFAULT NULL,
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_description TEXT DEFAULT NULL,
        views INT DEFAULT 0,
        status ENUM('published', 'draft') DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug),
        INDEX idx_category (category),
        INDEX idx_event_date (event_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Created current_affairs table successfully\n";

    // Check if sample data exists
    $count = (int)$pdo->query("SELECT COUNT(*) FROM current_affairs")->fetchColumn();

    if ($count === 0) {
        $sampleData = [
            [
                'title' => 'ISRO Successfully Executes Gaganyaan Pathfinder Mission 2026',
                'slug' => 'isro-gaganyaan-pathfinder-mission-2026',
                'category' => 'Science & Tech',
                'description' => "### ISRO Gaganyaan Pathfinder Mission 2026\n\nThe Indian Space Research Organisation (**ISRO**) achieved a historic milestone by successfully launching and recovering the **Gaganyaan Pathfinder Crew Module** in the Bay of Bengal.\n\n#### Key Highlights:\n- **Launch Vehicle:** LVM3-M5 rocket from Satish Dhawan Space Centre, Sriharikota.\n- **Mission Objective:** Validation of Emergency Escape System and atmospheric re-entry parameters.\n- **Significance:** Paves the way for India's first crewed spaceflight scheduled for late 2026.",
                'event_date' => '2026-08-20',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'ISRO Gaganyaan Pathfinder Mission 2026 - Current Affairs',
                'meta_description' => 'ISRO successfully executes Gaganyaan Pathfinder mission validating crew escape and re-entry systems for India spaceflight program.',
                'views' => 1420
            ],
            [
                'title' => 'RBI Introduces Offline Digital Rupee (e₹) Framework for Rural Banking',
                'slug' => 'rbi-offline-digital-rupee-framework-2026',
                'category' => 'Economy',
                'description' => "### RBI Offline Digital Rupee (e₹) Framework\n\nThe Reserve Bank of India (**RBI**) has launched a pioneering offline transaction feature for the Central Bank Digital Currency (**CBDC / e₹**), enabling seamless payments in areas with limited or zero internet connectivity.\n\n#### Key Features:\n- **Technology:** Proximity-based NFC and encrypted sound-wave transaction protocols.\n- **Limit:** Transaction cap set at ₹2,000 per offline payment for security.\n- **Target Benefit:** Rural financial inclusion, hilly terrains, and disaster-prone zones.",
                'event_date' => '2026-08-18',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'RBI Offline Digital Rupee Framework 2026 - Current Affairs',
                'meta_description' => 'Reserve Bank of India launches offline e-Rupee digital currency framework for rural and low-connectivity transactions.',
                'views' => 980
            ],
            [
                'title' => 'Union Cabinet Approves National Green Hydrogen Corridor Project',
                'slug' => 'union-cabinet-green-hydrogen-corridor-2026',
                'category' => 'National',
                'description' => "### National Green Hydrogen Corridor Project\n\nThe Union Cabinet chaired by the Prime Minister has approved a ₹19,744 crore allocation for setting up Dedicated **Green Hydrogen Corridors** across major industrial hubs in India.\n\n#### Key Objectives:\n- **Production Target:** 5 Million Metric Tonnes (MMT) per annum by 2030.\n- **Emissions Reduction:** Expected abatement of nearly 50 million metric tonnes of CO2 annually.\n- **Job Creation:** Over 6 lakh clean energy jobs projected across Gujarat, Odisha, and Tamil Nadu.",
                'event_date' => '2026-08-15',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'National Green Hydrogen Corridor 2026 - Current Affairs',
                'meta_description' => 'Union Cabinet approves Green Hydrogen Corridor project to boost renewable energy infrastructure and clean jobs.',
                'views' => 2100
            ],
            [
                'title' => 'India Clinches Top Spot at Asian Athletics Championships 2026',
                'slug' => 'india-top-spot-asian-athletics-championships-2026',
                'category' => 'Sports',
                'description' => "### Asian Athletics Championships 2026\n\nIndian contingent delivered a stellar performance at the **26th Asian Athletics Championships**, finishing at the top of the medal tally with a record haul of **29 Medals** (12 Gold, 9 Silver, 8 Bronze).\n\n#### Star Performers:\n- **Javelin Throw:** Gold medal secured with a championship record throw of 89.45m.\n- **Women's 400m Relay:** Gold medal with a national record timing.\n- **Steeplechase:** Double podium finish in 3000m men's event.",
                'event_date' => '2026-08-12',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'India Asian Athletics Championships 2026 - Sports Current Affairs',
                'meta_description' => 'India finishes top of the medal tally at Asian Athletics Championships 2026 with 29 total medals.',
                'views' => 1750
            ],
            [
                'title' => 'G20 Summit 2026 Adopts Universal Artificial Intelligence Governance Treaty',
                'slug' => 'g20-summit-2026-ai-governance-treaty',
                'category' => 'International',
                'description' => "### G20 AI Governance Treaty 2026\n\nLeaders at the G20 Leaders Summit unanimously signed the **Global AI Governance & Ethics Treaty**, establishing shared standards for safe artificial intelligence deployment, data privacy, and deepfake prevention.\n\n#### Core Pillars:\n- **AI Transparency:** Mandated watermarking for synthetic media and LLM outputs.\n- **Inclusive Growth:** Special $10 Billion compute access fund for developing nations.\n- **Cybersecurity Cooperation:** Joint international taskforce to prevent AI-driven cyber attacks.",
                'event_date' => '2026-08-08',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'G20 Summit 2026 AI Ethics Treaty - International News',
                'meta_description' => 'G20 Summit 2026 adopts landmark global AI ethics and governance framework to ensure safe AI deployment.',
                'views' => 3100
            ],
            [
                'title' => 'National Sports Awards 2026 Announced by Ministry of Youth Affairs',
                'slug' => 'national-sports-awards-2026-announced',
                'category' => 'Awards & Honours',
                'description' => "### National Sports Awards 2026\n\nThe Ministry of Youth Affairs and Sports announced the prestigious **Major Dhyan Chand Khel Ratna** and **Arjuna Awards** for outstanding sporting contributions in 2026.\n\n#### Key Awardees:\n- **Khel Ratna Award:** Awarded to top international athletes in Athletics and Badminton.\n- **Dronacharya Award:** Honored legendary coaches in Boxing and Wrestling.\n- **Rashtriya Khel Protsahan Puraskar:** Presented to public sector undertakings supporting grassroots sports academies.",
                'event_date' => '2026-08-05',
                'thumbnail' => '/assets/logo/fc_logo_crop.webp',
                'pdf_link' => null,
                'meta_title' => 'National Sports Awards 2026 - Awards & Honours GK',
                'meta_description' => 'Ministry of Youth Affairs announces Khel Ratna, Arjuna Awards, and Dronacharya Awards 2026.',
                'views' => 1280
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO current_affairs (title, slug, category, description, event_date, thumbnail, pdf_link, meta_title, meta_description, views, status) VALUES (:title, :slug, :category, :description, :event_date, :thumbnail, :pdf_link, :meta_title, :meta_description, :views, 'published')");

        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        echo "✓ Seeded " . count($sampleData) . " sample current affairs articles successfully\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
