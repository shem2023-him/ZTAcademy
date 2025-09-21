<?php
// zta_course.php
$modules = [
    [
        "title" => "Module 1: Introduction to Zero Trust",
        "description" => "Understand the concept of Zero Trust, its benefits, and why traditional perimeter-based security is insufficient.",
        "notes" => [
            "Zero Trust assumes 'never trust, always verify.'",
            "Traditional security relies on firewalls and VPNs, which trust internal network traffic.",
            "ZTA shifts focus from network perimeter security to identity, devices, and data.",
            "Key concept: least privilege – giving users only the access they need."
        ],
        "references" => [
            "NIST SP 800-207: Zero Trust Architecture",
            "Rose, S., et al. (2020). Zero Trust Architecture, National Institute of Standards and Technology"
        ]
    ],
    [
        "title" => "Module 2: Core Principles of Zero Trust",
        "description" => "Learn the guiding principles of Zero Trust and how identity, device, and access policies enforce security.",
        "notes" => [
            "Core principles:",
            "1. Verify Explicitly – Authenticate and authorize every access request.",
            "2. Use Least Privilege Access – Limit access to only what is needed.",
            "3. Assume Breach – Always monitor and log activity to detect threats.",
            "Multi-factor authentication (MFA), strong encryption, micro-segmentation, and continuous monitoring are key components."
        ],
        "references" => [
            "Kindervag, J. (2010). Build Security Into Your Network’s DNA: The Zero Trust Model. Forrester Research",
            "NIST SP 800-207"
        ]
    ],
    [
        "title" => "Module 3: Components and Technologies",
        "description" => "Explore the components that support ZTA and the technologies used for implementation.",
        "notes" => [
            "Identity and Access Management (IAM) is central to verifying users and devices.",
            "Device Security ensures endpoints meet compliance before granting access.",
            "Micro-segmentation isolates applications and data to reduce lateral movement.",
            "Policy Enforcement: Software-defined policies control access.",
            "Continuous Monitoring and Analytics detect anomalous behavior."
        ],
        "references" => [
            "Kindervag, J., NIST SP 800-207",
            "Scott, S., & Kindervag, J. (2019). Implementing Zero Trust Networks"
        ]
    ],
    [
        "title" => "Module 4: Implementation Strategies",
        "description" => "Learn how organizations implement ZTA and best practices for a phased approach.",
        "notes" => [
            "Phased approach recommended:",
            "1. Assess current security posture",
            "2. Define sensitive assets and data",
            "3. Implement IAM and least privilege access",
            "4. Apply micro-segmentation and network monitoring",
            "5. Continuously update policies and monitor for threats",
            "Challenges include legacy systems, cultural resistance, and complexity of continuous monitoring."
        ],
        "references" => [
            "Rose, S., et al., NIST SP 800-207",
            "Cybersecurity & Infrastructure Security Agency (CISA) resources"
        ]
    ],
    [
        "title" => "Module 5: Case Studies & Emerging Trends",
        "description" => "Explore real-world applications of ZTA and understand trends and future directions.",
        "notes" => [
            "Companies like Google, Microsoft, and IBM have implemented Zero Trust frameworks.",
            "Trend: Integration of AI/ML for threat detection and automated policy adjustments.",
            "ZTA is expanding into cloud environments, SaaS applications, and hybrid networks."
        ],
        "references" => [
            "Google BeyondCorp Case Study",
            "Microsoft Zero Trust deployment guide"
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zero Trust Architecture Course</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        h2 {
            color: #34495e;
        }
        p.description {
            font-size: 16px;
            margin-bottom: 10px;
        }
        ul {
            margin: 0 0 20px 20px;
        }
        li {
            margin-bottom: 6px;
        }
        .references {
            font-size: 14px;
            color: #555;
        }
        hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Zero Trust Architecture (ZTA) Course</h1>

        <?php foreach ($modules as $module): ?>
            <hr>
            <h2><?php echo $module['title']; ?></h2>
            <p class="description"><?php echo $module['description']; ?></p>

            <h3>Notes:</h3>
            <ul>
                <?php foreach ($module['notes'] as $note): ?>
                    <li><?php echo $note; ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>References:</h3>
            <ul class="references">
                <?php foreach ($module['references'] as $ref): ?>
                    <li><?php echo $ref; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</body>
</html>
