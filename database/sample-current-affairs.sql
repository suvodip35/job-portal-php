-- Current Affairs Table Schema and Sample Data Seed Script for FromCampus

CREATE TABLE IF NOT EXISTS `current_affairs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `category` VARCHAR(100) DEFAULT 'National',
    `description` LONGTEXT NOT NULL,
    `event_date` DATE DEFAULT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `pdf_link` VARCHAR(255) DEFAULT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `views` INT DEFAULT 0,
    `status` ENUM('published', 'draft') DEFAULT 'published',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category` (`category`),
    INDEX `idx_event_date` (`event_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Current Affairs Data

INSERT INTO `current_affairs` (`title`, `slug`, `category`, `description`, `event_date`, `thumbnail`, `pdf_link`, `meta_title`, `meta_description`, `views`, `status`) VALUES
(
    'ISRO Successfully Executes Gaganyaan Pathfinder Mission 2026',
    'isro-gaganyaan-pathfinder-mission-2026',
    'Science & Tech',
    '### ISRO Gaganyaan Pathfinder Mission 2026\n\nThe Indian Space Research Organisation (**ISRO**) achieved a historic milestone by successfully launching and recovering the **Gaganyaan Pathfinder Crew Module** in the Bay of Bengal.\n\n#### Key Highlights:\n- **Launch Vehicle:** LVM3-M5 rocket from Satish Dhawan Space Centre, Sriharikota.\n- **Mission Objective:** Validation of Emergency Escape System and atmospheric re-entry parameters.\n- **Significance:** Paves the way for India\'s first crewed spaceflight scheduled for late 2026.',
    '2026-08-20',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'ISRO Gaganyaan Pathfinder Mission 2026 - Current Affairs',
    'ISRO successfully executes Gaganyaan Pathfinder mission validating crew escape and re-entry systems for India spaceflight program.',
    1420,
    'published'
),
(
    'RBI Introduces Offline Digital Rupee (e₹) Framework for Rural Banking',
    'rbi-offline-digital-rupee-framework-2026',
    'Economy',
    '### RBI Offline Digital Rupee (e₹) Framework\n\nThe Reserve Bank of India (**RBI**) has launched a pioneering offline transaction feature for the Central Bank Digital Currency (**CBDC / e₹**), enabling seamless payments in areas with limited or zero internet connectivity.\n\n#### Key Features:\n- **Technology:** Proximity-based NFC and encrypted sound-wave transaction protocols.\n- **Limit:** Transaction cap set at ₹2,000 per offline payment for security.\n- **Target Benefit:** Rural financial inclusion, hilly terrains, and disaster-prone zones.',
    '2026-08-18',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'RBI Offline Digital Rupee Framework 2026 - Current Affairs',
    'Reserve Bank of India launches offline e-Rupee digital currency framework for rural and low-connectivity transactions.',
    980,
    'published'
),
(
    'Union Cabinet Approves National Green Hydrogen Corridor Project',
    'union-cabinet-green-hydrogen-corridor-2026',
    'National',
    '### National Green Hydrogen Corridor Project\n\nThe Union Cabinet chaired by the Prime Minister has approved a ₹19,744 crore allocation for setting up Dedicated **Green Hydrogen Corridors** across major industrial hubs in India.\n\n#### Key Objectives:\n- **Production Target:** 5 Million Metric Tonnes (MMT) per annum by 2030.\n- **Emissions Reduction:** Expected abatement of nearly 50 million metric tonnes of CO2 annually.\n- **Job Creation:** Over 6 lakh clean energy jobs projected across Gujarat, Odisha, and Tamil Nadu.',
    '2026-08-15',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'National Green Hydrogen Corridor 2026 - Current Affairs',
    'Union Cabinet approves Green Hydrogen Corridor project to boost renewable energy infrastructure and clean jobs.',
    2100,
    'published'
),
(
    'India Clinches Top Spot at Asian Athletics Championships 2026',
    'india-top-spot-asian-athletics-championships-2026',
    'Sports',
    '### Asian Athletics Championships 2026\n\nIndian contingent delivered a stellar performance at the **26th Asian Athletics Championships**, finishing at the top of the medal tally with a record haul of **29 Medals** (12 Gold, 9 Silver, 8 Bronze).\n\n#### Star Performers:\n- **Javelin Throw:** Gold medal secured with a championship record throw of 89.45m.\n- **Women\'s 400m Relay:** Gold medal with a national record timing.\n- **Steeplechase:** Double podium finish in 3000m men\'s event.',
    '2026-08-12',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'India Asian Athletics Championships 2026 - Sports Current Affairs',
    'India finishes top of the medal tally at Asian Athletics Championships 2026 with 29 total medals.',
    1750,
    'published'
),
(
    'G20 Summit 2026 Adopts Universal Artificial Intelligence Governance Treaty',
    'g20-summit-2026-ai-governance-treaty',
    'International',
    '### G20 AI Governance Treaty 2026\n\nLeaders at the G20 Leaders Summit unanimously signed the **Global AI Governance & Ethics Treaty**, establishing shared standards for safe artificial intelligence deployment, data privacy, and deepfake prevention.\n\n#### Core Pillars:\n- **AI Transparency:** Mandated watermarking for synthetic media and LLM outputs.\n- **Inclusive Growth:** Special $10 Billion compute access fund for developing nations.\n- **Cybersecurity Cooperation:** Joint international taskforce to prevent AI-driven cyber attacks.',
    '2026-08-08',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'G20 Summit 2026 AI Ethics Treaty - International News',
    'G20 Summit 2026 adopts landmark global AI ethics and governance framework to ensure safe AI deployment.',
    3100,
    'published'
),
(
    'National Sports Awards 2026 Announced by Ministry of Youth Affairs',
    'national-sports-awards-2026-announced',
    'Awards & Honours',
    '### National Sports Awards 2026\n\nThe Ministry of Youth Affairs and Sports announced the prestigious **Major Dhyan Chand Khel Ratna** and **Arjuna Awards** for outstanding sporting contributions in 2026.\n\n#### Key Awardees:\n- **Khel Ratna Award:** Awarded to top international athletes in Athletics and Badminton.\n- **Dronacharya Award:** Honored legendary coaches in Boxing and Wrestling.\n- **Rashtriya Khel Protsahan Puraskar:** Presented to public sector undertakings supporting grassroots sports academies.',
    '2026-08-05',
    '/assets/logo/fc_logo_crop.webp',
    NULL,
    'National Sports Awards 2026 - Awards & Honours GK',
    'Ministry of Youth Affairs announces Khel Ratna, Arjuna Awards, and Dronacharya Awards 2026.',
    1280,
    'published'
)
ON DUPLICATE KEY UPDATE `views` = VALUES(`views`);
