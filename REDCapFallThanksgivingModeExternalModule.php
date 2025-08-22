<?php
namespace Vanderbilt\REDCapFallThanksgivingModeExternalModule;

use ExternalModules\AbstractExternalModule;

class REDCapFallThanksgivingModeExternalModule extends AbstractExternalModule
{
    private function isModuleEnabled()
    {
        try {
            // Check if the module is enabled by trying to access a system setting
            // This will throw an exception if the module is disabled
            $this->getSystemSetting('enable-fall-mode-global');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function redcap_every_page_top($project_id = null)
    {
        try {
            // Check if module is enabled before proceeding
            if (!$this->isModuleEnabled()) {
                return;
            }
            
            if (!$this->shouldEnableFallMode($project_id)) {
                return;
            }

            $this->addFallAssets($project_id);
        } catch (Exception $e) {
            error_log("REDCap Fall & Thanksgiving Mode Module Error: " . $e->getMessage());
        }
    }

    private function shouldEnableFallMode($project_id = null)
    {
        // Check if module is globally enabled first
        $globalEnabled = $this->getSystemSetting('enable-fall-mode-global');
        if ($globalEnabled) {
            return true;
        }
        
        // If not global, check project-specific settings
        if ($project_id) {
            // Check if there are any project settings configured
            $hasLeaves = $this->getProjectSetting('enable-leaves-animation', $project_id);
            $hasStyle = $this->getProjectSetting('fall-theme-style', $project_id);
            $hasIntensity = $this->getProjectSetting('fall-theme-intensity', $project_id);
            
            // If any setting is configured, enable the mode
            return $hasLeaves || $hasStyle || $hasIntensity;
        }
        
        return false;
    }

    private function addFallAssets($project_id = null)
    {
        // Get intensity from project settings first, then system settings, default to moderate
        $intensity = $this->getProjectSetting('fall-theme-intensity', $project_id) ?: 
                    $this->getSystemSetting('fall-theme-intensity') ?: 'moderate';
        
        $enableLeaves = $this->getProjectSetting('enable-leaves-animation', $project_id) ?? true;
        $themeStyle = $this->getProjectSetting('fall-theme-style', $project_id) ?: 'classic';
        
        // Generate theme and intensity styles
        $themeStyles = $this->getThemeStyles($themeStyle);
        $intensityStyles = $this->getThemeAwareIntensityStyles($intensity, $themeStyle);
        
        echo '<style>
        /* Fall Theme Base Colors and Variables */
        ' . $themeStyles . '
        
        /* Theme-Aware Intensity Adjustments */
        ' . $intensityStyles . '
        
        /* Main Fall Theme */
        body.fall-mode {
            background: var(--fall-bg) !important;
            min-height: 100vh;
            position: relative;
        }
        
        body.fall-mode::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 30% 70%, rgba(218, 165, 32, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(139, 69, 19, 0.15) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Header Fall Styling */
        body.fall-mode #container,
        body.fall-mode #center,
        body.fall-mode #wrapper {
            background: rgba(245, 245, 220, 0.95) !important;
            border-radius: 10px;
            margin: 10px;
            box-shadow: 0 0 20px rgba(139, 69, 19, 0.4);
            border: 2px solid var(--fall-gold);
        }
        
        /* Fall Headers */
        body.fall-mode h1,
        body.fall-mode h2,
        body.fall-mode h3 {
            color: var(--fall-burgundy) !important;
            text-shadow: 1px 1px 2px rgba(218, 165, 32, 0.3);
            position: relative;
        }
        
        body.fall-mode h1::after {
            content: "🍂";
            margin-left: 10px;
            animation: flutter 3s ease-in-out infinite;
        }
        
        /* Fall Tables */
        body.fall-mode table {
            background: rgba(245, 245, 220, 0.9) !important;
            border: 2px solid var(--fall-brown) !important;
            border-radius: 8px;
            overflow: hidden;
        }
        
        body.fall-mode table th {
            background: var(--fall-burgundy) !important;
            color: var(--fall-cream) !important;
            border: 1px solid var(--fall-gold) !important;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        
        body.fall-mode table td {
            background: rgba(245, 245, 220, 0.95) !important;
            border: 1px solid var(--fall-brown) !important;
        }
        
        body.fall-mode table tbody tr:nth-child(even) td {
            background: rgba(210, 105, 30, 0.1) !important;
        }
        
        body.fall-mode table tbody tr:hover td {
            background: rgba(218, 165, 32, 0.25) !important;
        }
        
        /* Fall Forms */
        body.fall-mode input,
        body.fall-mode select,
        body.fall-mode textarea {
            background: rgba(245, 245, 220, 0.95) !important;
            border: 2px solid var(--fall-brown) !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        body.fall-mode input:focus,
        body.fall-mode select:focus,
        body.fall-mode textarea:focus {
            border-color: var(--fall-burgundy) !important;
            box-shadow: 0 0 10px rgba(128, 0, 32, 0.3) !important;
        }
        
        /* Fall Buttons */
        body.fall-mode .btn,
        body.fall-mode button {
            background: linear-gradient(45deg, var(--fall-orange), var(--fall-red)) !important;
            color: var(--fall-cream) !important;
            border: 2px solid var(--fall-gold) !important;
            border-radius: 20px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        
        body.fall-mode .btn:hover,
        body.fall-mode button:hover {
            background: linear-gradient(45deg, var(--fall-brown), var(--fall-dark-brown)) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Fall Menu */
        body.fall-mode .menubox {
            background: rgba(139, 69, 19, 0.95) !important;
            border: 2px solid var(--fall-gold) !important;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(218, 165, 32, 0.4);
        }
        
        body.fall-mode .menubox a {
            color: var(--fall-cream) !important;
            transition: all 0.3s ease;
        }
        
        body.fall-mode .menubox a:hover {
            background: rgba(210, 105, 30, 0.8) !important;
            color: var(--fall-gold) !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        /* Fall Links */
        body.fall-mode a {
            color: var(--fall-burgundy) !important;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        
        body.fall-mode a:hover {
            color: var(--fall-orange) !important;
            text-decoration: underline;
        }
        
        /* Brighten gray descriptive text for better visibility */
        body.fall-mode .menubox div:not(:first-child),
        body.fall-mode .menubox div div,
        body.fall-mode .menubox font[color="#777777"],
        body.fall-mode .menubox font[color="#888888"],
        body.fall-mode .menubox font[color="#999999"],
        body.fall-mode .menubox font[color="gray"],
        body.fall-mode .menubox .gray,
        body.fall-mode .menubox small,
        body.fall-mode div[style*="color:#777"],
        body.fall-mode div[style*="color:#888"],
        body.fall-mode div[style*="color:#999"],
        body.fall-mode div[style*="color:gray"],
        body.fall-mode font[style*="color:#777"],
        body.fall-mode font[style*="color:#888"],
        body.fall-mode font[style*="color:#999"],
        body.fall-mode font[style*="color:gray"] {
            color: #2c1810 !important;
        }
        
        
        /* Pumpkin Spice Mode Extra Warmth */
        body.fall-mode.pumpkin-spice-mode {
            filter: sepia(20%) saturate(120%) hue-rotate(15deg);
        }
        
        /* Animations */
        @keyframes flutter {
            0%, 100% { transform: rotate(-5deg); opacity: 1; }
            25% { transform: rotate(5deg); opacity: 0.8; }
            50% { transform: rotate(-3deg); opacity: 1; }
            75% { transform: rotate(3deg); opacity: 0.9; }
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0px); }
            50% { transform: translateX(-50%) translateY(-15px); }
        }
        
        @keyframes leaffall {
            0% { 
                transform: translateY(-100vh) translateX(0px) rotate(0deg); 
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { 
                transform: translateY(100vh) translateX(var(--drift)) rotate(720deg); 
                opacity: 0;
            }
        }
        
        @keyframes gentle-sway {
            0%, 100% { transform: translateX(0px); }
            25% { transform: translateX(15px); }
            75% { transform: translateX(-15px); }
        }
        
        @keyframes tumbleleaf {
            0% { transform: translateY(-100vh) rotate(0deg); }
            100% { transform: translateY(100vh) rotate(360deg); }
        }
        
        /* Tumbling Leaf Animation */
        .tumbleleaf {
            position: fixed;
            top: -10px;
            color: #d2691e;
            font-size: 1em;
            animation: tumbleleaf linear infinite;
            pointer-events: none;
            z-index: 1000;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        
        /* Falling Leaves Animation */
        .falling-leaf {
            position: fixed;
            top: -20px;
            font-size: 1.2em;
            animation: leaffall linear infinite, gentle-sway ease-in-out infinite;
            pointer-events: none;
            z-index: 1000;
            opacity: 0.8;
        }
        
        </style>';
        
        echo '<script>
        function toggleFallMode() {
            console.log("toggleFallMode called");
            const body = document.body;
            
            if (body.classList.contains("fall-mode")) {
                body.classList.remove("fall-mode");
                body.classList.remove("pumpkin-spice-mode");
                localStorage.setItem("redcap-fall-mode", "false");
                updateToggleButton(false);
                restoreOriginalLogos();
                clearFallingLeaves();
                clearTumblingLeaves();
                console.log("Fall mode disabled");
            } else {
                body.classList.add("fall-mode");
                ' . ($themeStyle === 'pumpkin-spice' ? 'body.classList.add("pumpkin-spice-mode");' : '') . '
                localStorage.setItem("redcap-fall-mode", "true");
                updateToggleButton(true);
                replaceLogosWithFallVersion();
                ' . ($enableLeaves ? 'createFallingLeaves();' : '') . '
                ' . ($enableLeaves ? 'createTumblingLeaves();' : '') . '
                console.log("Fall mode enabled");
            }
        }
        
        function updateToggleButton(isFall) {
            const button = document.getElementById("fall-mode-toggle");
            if (button) {
                button.textContent = isFall ? "🌻 Normal Mode" : "🍂 Fall Mode";
                if (isFall) {
                    button.classList.add("fall-mode");
                } else {
                    button.classList.remove("fall-mode");
                }
            }
        }
        
        function createFallingLeaves() {
            const leaves = ["🍂", "🍁", "🌿", "🍃", "🌾"];
            const container = document.body;
            
            // Create fewer leaves for better performance and aesthetics
            for (let i = 0; i < 20; i++) {
                setTimeout(() => {
                    const leaf = document.createElement("div");
                    leaf.className = "falling-leaf";
                    leaf.innerHTML = leaves[Math.floor(Math.random() * leaves.length)];
                    
                    // Random horizontal position
                    leaf.style.left = Math.random() * 100 + "vw";
                    
                    // Slower, more varied animation duration (10-25 seconds)
                    const duration = Math.random() * 15 + 10;
                    leaf.style.animationDuration = duration + "s, " + (Math.random() * 6 + 4) + "s";
                    
                    // More varied opacity (0.4 to 0.9)
                    leaf.style.opacity = Math.random() * 0.5 + 0.4;
                    
                    // More varied size (10-20px)
                    leaf.style.fontSize = (Math.random() * 10 + 10) + "px";
                    
                    // Random horizontal drift
                    const drift = (Math.random() - 0.5) * 150; // -75px to +75px
                    leaf.style.setProperty("--drift", drift + "px");
                    
                    // Random delay for gentle sway animation
                    leaf.style.animationDelay = "0s, " + (Math.random() * 3) + "s";
                    
                    // Random fall colors
                    const fallColors = ["#d2691e", "#b22222", "#daa520", "#8b4513", "#800020"];
                    leaf.style.color = fallColors[Math.floor(Math.random() * fallColors.length)];
                    
                    container.appendChild(leaf);
                    
                    // Remove after animation completes
                    setTimeout(() => {
                        if (leaf.parentNode) {
                            leaf.parentNode.removeChild(leaf);
                        }
                    }, duration * 1000 + 1000);
                }, Math.random() * 8000); // Spread creation over 8 seconds
            }
        }
        
        function clearFallingLeaves() {
            const leaves = document.querySelectorAll(".falling-leaf");
            leaves.forEach(leaf => {
                if (leaf.parentNode) {
                    leaf.parentNode.removeChild(leaf);
                }
            });
        }
        
        function createTumblingLeaves() {
            const leaves = ["🍂", "🍁", "🌿"];
            const container = document.body;
            
            for (let i = 0; i < 4; i++) {
                setTimeout(() => {
                    const leaf = document.createElement("div");
                    leaf.className = "tumbleleaf";
                    leaf.innerHTML = leaves[Math.floor(Math.random() * leaves.length)];
                    leaf.style.left = Math.random() * 100 + "vw";
                    leaf.style.animationDuration = Math.random() * 6 + 4 + "s";
                    leaf.style.opacity = Math.random();
                    leaf.style.fontSize = (Math.random() * 10 + 10) + "px";
                    
                    // Random fall colors - fern, yellow, red only
                    const fallColors = ["#228B22", "#FFD700", "#DC143C"];
                    leaf.style.color = fallColors[Math.floor(Math.random() * fallColors.length)];
                    
                    container.appendChild(leaf);
                    
                    setTimeout(() => {
                        if (leaf.parentNode) {
                            leaf.parentNode.removeChild(leaf);
                        }
                    }, 10000);
                }, Math.random() * 2000);
            }
        }
        
        function clearTumblingLeaves() {
            const leaves = document.querySelectorAll(".tumbleleaf");
            leaves.forEach(leaf => {
                if (leaf.parentNode) {
                    leaf.parentNode.removeChild(leaf);
                }
            });
        }
        
        function replaceLogosWithFallVersion() {
            const logos = document.querySelectorAll("img[src*=\"redcap-logo\"]");
            logos.forEach(logo => {
                if (!logo.dataset.originalSrc) {
                    logo.dataset.originalSrc = logo.src;
                }
                const modulePath = "/redcap1532/modules/fall_thanksgiving_mode_v1.0.0/";
                logo.src = modulePath + "leaf_covered_redcap_logo_final_v5.png";
            });
        }
        
        function restoreOriginalLogos() {
            const logos = document.querySelectorAll("img[src*=\"leaf_covered_redcap_logo_final_v5\"]");
            logos.forEach(logo => {
                if (logo.dataset.originalSrc) {
                    logo.src = logo.dataset.originalSrc;
                }
            });
        }
        
        function initFallMode() {
            console.log("Initializing Fall mode...");
            const saved = localStorage.getItem("redcap-fall-mode");
            
            if (saved === "true") {
                document.body.classList.add("fall-mode");
                ' . ($themeStyle === 'pumpkin-spice' ? 'document.body.classList.add("pumpkin-spice-mode");' : '') . '
                updateToggleButton(true);
                replaceLogosWithFallVersion();
                ' . ($enableLeaves ? 'createFallingLeaves();' : '') . '
                ' . ($enableLeaves ? 'createTumblingLeaves();' : '') . '
                console.log("Applied saved Fall mode");
            } else {
                updateToggleButton(false);
            }
        }
        
        // Initialize when DOM is ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initFallMode);
        } else {
            initFallMode();
        }
        
        // Continuous leaf fall - slower interval for natural feel
        ' . ($enableLeaves ? 'setInterval(() => {
            if (document.body.classList.contains("fall-mode")) {
                createFallingLeaves();
            }
        }, 20000);' : '') . ' // Create new batch every 20 seconds
        
        // Continuous tumbling leaves every 2-3 seconds
        ' . ($enableLeaves ? 'setInterval(() => {
            if (document.body.classList.contains("fall-mode")) {
                createTumblingLeaves();
            }
        }, Math.random() * 1000 + 2000);' : '') . '
        </script>';
        
        $this->addToggleButton();
    }

    private function addToggleButton()
    {
        ?>
        <style>
        .fall-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: linear-gradient(45deg, #d2691e, #b22222);
            color: #f5f5dc;
            border: 2px solid #b8860b;
            border-radius: 25px;
            padding: 10px 18px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.4);
            transition: all 0.3s ease;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
        }
        .fall-mode-toggle:hover {
            background: linear-gradient(45deg, #8b4513, #654321);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 69, 19, 0.6);
        }
        .fall-mode-toggle.fall-mode {
            background: linear-gradient(45deg, #8b4513, #654321);
            border-color: #b8860b;
        }
        .fall-mode-toggle.fall-mode:hover {
            background: linear-gradient(45deg, #d2691e, #b22222);
        }
        </style>
        <button id="fall-mode-toggle" class="fall-mode-toggle" onclick="toggleFallMode()">
            🍂 Fall Mode
        </button>
        <?php
    }

    private function getThemeAwareIntensityStyles($intensity, $themeStyle)
    {
        // Get theme-specific colors for buttons and nav
        $lightButtonColors = ($themeStyle === 'pumpkin-spice') 
            ? 'linear-gradient(45deg, #ff8c42, #ff6b35)' 
            : 'linear-gradient(45deg, #daa520, #b8860b)';
        
        $lightNavColors = ($themeStyle === 'pumpkin-spice') 
            ? 'rgba(255, 170, 68, 0.95)' 
            : 'rgba(245, 245, 220, 0.95)';
        
        $lightNavBorder = ($themeStyle === 'pumpkin-spice') 
            ? '#ff8c42' 
            : '#daa520';

        switch ($intensity) {
            case 'light':
                return "
                /* Light Intensity - Minimal autumn feel */
                body.fall-mode {
                    background: linear-gradient(135deg, #faf9f7 0%, #f7f4f0 50%, #f4f0e8 100%) !important;
                    filter: none;
                }
                body.fall-mode::before {
                    background: none !important;
                }
                body.fall-mode #container,
                body.fall-mode #center,
                body.fall-mode #wrapper {
                    background: rgba(255, 255, 255, 0.98) !important;
                    box-shadow: 0 0 5px rgba(139, 69, 19, 0.1);
                    border: 1px solid rgba(218, 165, 32, 0.2);
                    border-radius: 8px;
                    margin: 10px;
                }
                body.fall-mode table th {
                    background: $lightButtonColors !important;
                    color: white !important;
                    opacity: 0.8;
                    box-shadow: none;
                    text-shadow: none;
                }
                body.fall-mode table td {
                    background: rgba(255, 255, 255, 0.98) !important;
                }
                body.fall-mode table tbody tr:nth-child(even) td {
                    background: rgba(245, 245, 220, 0.5) !important;
                }
                body.fall-mode .btn,
                body.fall-mode button {
                    background: $lightButtonColors !important;
                    color: white !important;
                    border: 1px solid var(--fall-gold) !important;
                    border-radius: 15px;
                    padding: 6px 12px;
                    box-shadow: none;
                    text-shadow: none;
                    transform: none;
                }
                body.fall-mode h1,
                body.fall-mode h2,
                body.fall-mode h3 {
                    color: #8b4513 !important;
                    text-shadow: none !important;
                }
                body.fall-mode h1::after {
                    content: '🍂';
                    margin-left: 8px;
                    font-size: 0.8em;
                    opacity: 0.6;
                    animation: none;
                }
                body.fall-mode .menubox {
                    background: $lightNavColors !important;
                    border: 1px solid $lightNavBorder !important;
                    border-radius: 8px;
                    box-shadow: 0 0 5px rgba(139, 69, 19, 0.1);
                }
                body.fall-mode .menubox a {
                    color: #8b4513 !important;
                }
                /* Minimal falling leaves */
                .falling-leaf, .tumbleleaf {
                    opacity: 0.2 !important;
                    font-size: 0.8em !important;
                }";
                
            case 'full':
            default:
                return '
                /* Full Intensity - Maximum autumn experience */
                body.fall-mode {
                    filter: sepia(20%) saturate(120%) contrast(105%) brightness(95%);
                    animation: seasonal-pulse 6s ease-in-out infinite alternate;
                }
                @keyframes seasonal-pulse {
                    0% { filter: sepia(20%) saturate(120%) contrast(105%) brightness(95%); }
                    100% { filter: sepia(25%) saturate(130%) contrast(110%) brightness(90%); }
                }
                body.fall-mode::before {
                    background: 
                        radial-gradient(circle at 25% 75%, rgba(var(--fall-brown-rgb), 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 75% 25%, rgba(var(--fall-orange-rgb), 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(var(--fall-gold-rgb), 0.1) 0%, transparent 70%);
                    animation: background-shift 8s ease-in-out infinite alternate;
                }
                @keyframes background-shift {
                    0% { opacity: 0.8; }
                    100% { opacity: 1; }
                }
                body.fall-mode #container,
                body.fall-mode #center,
                body.fall-mode #wrapper {
                    background: rgba(245, 245, 220, 0.95) !important;
                    box-shadow: 
                        0 0 30px rgba(var(--fall-brown-rgb), 0.6),
                        inset 0 0 15px rgba(var(--fall-gold-rgb), 0.15);
                    border: 3px solid var(--fall-gold);
                    border-radius: 12px;
                    margin: 10px;
                    position: relative;
                }
                body.fall-mode #container::after,
                body.fall-mode #center::after,
                body.fall-mode #wrapper::after {
                    content: "🍂🍁🌾🍂🍁🌾";
                    position: absolute;
                    top: -12px;
                    left: 50%;
                    transform: translateX(-50%);
                    font-size: 16px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    animation: decoration-bounce 2s ease-in-out infinite;
                    z-index: 10;
                }
                @keyframes decoration-bounce {
                    0%, 100% { transform: translateX(-50%) translateY(0px) scale(1); }
                    50% { transform: translateX(-50%) translateY(-3px) scale(1.05); }
                }
                body.fall-mode table th {
                    background: 
                        linear-gradient(45deg, var(--fall-burgundy), var(--fall-dark-brown)) !important;
                    color: var(--fall-cream) !important;
                    box-shadow: 
                        inset 0 0 15px rgba(0,0,0,0.3),
                        0 4px 8px rgba(var(--fall-brown-rgb), 0.5);
                    text-shadow: 2px 2px 3px rgba(0,0,0,0.5);
                    border: 2px solid var(--fall-gold) !important;
                }
                body.fall-mode table tbody tr:hover td {
                    background: rgba(var(--fall-gold-rgb), 0.3) !important;
                    transition: background 0.3s ease;
                }
                body.fall-mode .btn,
                body.fall-mode button {
                    background: 
                        linear-gradient(45deg, var(--fall-orange), var(--fall-red)) !important;
                    color: var(--fall-cream) !important;
                    border: 3px solid var(--fall-gold) !important;
                    border-radius: 25px;
                    padding: 10px 20px;
                    box-shadow: 
                        0 6px 12px rgba(var(--fall-brown-rgb), 0.5),
                        inset 0 1px 3px rgba(255,255,255,0.2);
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.4);
                    transform: translateY(-2px) scale(1.02);
                    transition: all 0.3s ease;
                }
                body.fall-mode .btn:hover,
                body.fall-mode button:hover {
                    background: 
                        linear-gradient(45deg, var(--fall-brown), var(--fall-dark-brown)) !important;
                    transform: translateY(-4px) scale(1.05);
                    box-shadow: 
                        0 8px 16px rgba(var(--fall-brown-rgb), 0.6),
                        inset 0 1px 3px rgba(255,255,255,0.3);
                }
                body.fall-mode h1,
                body.fall-mode h2,
                body.fall-mode h3 {
                    color: var(--fall-burgundy) !important;
                    text-shadow: 2px 2px 4px rgba(var(--fall-gold-rgb), 0.4);
                    position: relative;
                }
                body.fall-mode h1::after {
                    content: "🍂🍁🌾";
                    margin-left: 12px;
                    font-size: 1.1em;
                    animation: 
                        flutter 3s ease-in-out infinite,
                        color-shift 4s ease-in-out infinite alternate;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                @keyframes color-shift {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(15deg); }
                }
                body.fall-mode .menubox {
                    background: 
                        linear-gradient(135deg, rgba(var(--fall-brown-rgb), 0.95), rgba(var(--fall-dark-brown-rgb), 0.95)) !important;
                    border: 3px solid var(--fall-gold) !important;
                    border-radius: 12px;
                    box-shadow: 0 0 20px rgba(var(--fall-gold-rgb), 0.5);
                }
                body.fall-mode .menubox a {
                    color: var(--fall-cream) !important;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
                    transition: all 0.3s ease;
                }
                body.fall-mode .menubox a:hover {
                    background: rgba(var(--fall-orange-rgb), 0.8) !important;
                    color: var(--fall-gold) !important;
                    text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
                    transform: translateX(5px);
                }
                /* Enhanced falling leaves */
                .falling-leaf, .tumbleleaf {
                    opacity: 0.9 !important;
                    font-size: 1.3em !important;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
                }';
        }
    }
    
    private function getThemeStyles($themeStyle)
    {
        switch ($themeStyle) {
            case 'pumpkin-spice':
                return '
                /* Pumpkin Spice Theme Colors */
                :root {
                    --fall-orange: #ff6b35;
                    --fall-red: #d2001c;
                    --fall-yellow: #ffaa44;
                    --fall-brown: #8b4000;
                    --fall-burgundy: #722f37;
                    --fall-gold: #ff8c42;
                    --fall-cream: #ffeaa7;
                    --fall-dark-brown: #5d2e00;
                    --fall-bg: linear-gradient(135deg, #ff6b35 0%, #d2001c 25%, #ffaa44 50%, #8b4000 75%, #722f37 100%);
                    
                    /* RGB values for rgba() usage */
                    --fall-orange-rgb: 255, 107, 53;
                    --fall-red-rgb: 210, 0, 28;
                    --fall-yellow-rgb: 255, 170, 68;
                    --fall-brown-rgb: 139, 64, 0;
                    --fall-burgundy-rgb: 114, 47, 55;
                    --fall-gold-rgb: 255, 140, 66;
                    --fall-cream-rgb: 255, 234, 167;
                    --fall-dark-brown-rgb: 93, 46, 0;
                }
                
                body.fall-mode.pumpkin-spice-mode {
                    filter: sepia(15%) saturate(120%) hue-rotate(10deg) brightness(105%);
                }
                
                body.fall-mode.pumpkin-spice-mode table tbody tr:nth-child(even) td {
                    background: rgba(255, 170, 68, 0.15) !important;
                }
                
                body.fall-mode.pumpkin-spice-mode table tbody tr:hover td {
                    background: rgba(255, 107, 53, 0.25) !important;
                }
                
                body.fall-mode.pumpkin-spice-mode .menubox {
                    background: rgba(139, 64, 0, 0.95) !important;
                }';
                
            case 'classic':
            default:
                return '
                /* Classic Theme Colors */
                :root {
                    --fall-orange: #d2691e;
                    --fall-red: #b22222;
                    --fall-yellow: #daa520;
                    --fall-brown: #8b4513;
                    --fall-burgundy: #800020;
                    --fall-gold: #b8860b;
                    --fall-cream: #f5f5dc;
                    --fall-dark-brown: #654321;
                    --fall-bg: linear-gradient(135deg, #d2691e 0%, #b22222 25%, #daa520 50%, #8b4513 75%, #800020 100%);
                    
                    /* RGB values for rgba() usage */
                    --fall-orange-rgb: 210, 105, 30;
                    --fall-red-rgb: 178, 34, 34;
                    --fall-yellow-rgb: 218, 165, 32;
                    --fall-brown-rgb: 139, 69, 19;
                    --fall-burgundy-rgb: 128, 0, 32;
                    --fall-gold-rgb: 184, 134, 11;
                    --fall-cream-rgb: 245, 245, 220;
                    --fall-dark-brown-rgb: 101, 67, 33;
                }';
        }
    }
}