<?php
/**
 * All-in-One Multi-Toast Notification Component with Icons
 */

// NEW: Helper function to get the correct SVG icon based on the toast type.
// We define it here so it's only available when this component is included.
if (!function_exists('get_toast_svg')) {
    function get_toast_svg(string $type): string {
        // Using 'currentColor' for the stroke means the SVG will automatically
        // inherit the text color defined in your CSS for each toast type.
        $svg_attributes = 'class="toast-icon" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg"';

        switch ($type) {
            case 'success':
                return <<<SVG
                <svg {$svg_attributes}>
                <g filter="url(#filter0_d_1379_615)">
                <rect x="4" width="50" height="50" rx="5" fill="#23C35E"/>
                </g>
                <path d="M25.9272 29.0938L38.2866 16.7344C38.5782 16.4427 38.9185 16.2969 39.3074 16.2969C39.6963 16.2969 40.0366 16.4427 40.3282 16.7344C40.6199 17.026 40.7657 17.3726 40.7657 17.7742C40.7657 18.1757 40.6199 18.5218 40.3282 18.8125L26.948 32.2292C26.6564 32.5208 26.3161 32.6667 25.9272 32.6667C25.5383 32.6667 25.198 32.5208 24.9064 32.2292L18.6355 25.9583C18.3439 25.6667 18.2039 25.3206 18.2155 24.92C18.2272 24.5194 18.3793 24.1728 18.672 23.8802C18.9646 23.5876 19.3112 23.4417 19.7118 23.4427C20.1123 23.4437 20.4584 23.5895 20.7501 23.8802L25.9272 29.0938Z" fill="#F5F5F5"/>
                <defs>
                <filter id="filter0_d_1379_615" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                <feOffset dy="4"/>
                <feGaussianBlur stdDeviation="2"/>
                <feComposite in2="hardAlpha" operator="out"/>
                <feColorMatrix type="matrix" values="0 0 0 0 0.137255 0 0 0 0 0.764706 0 0 0 0 0.368627 0 0 0 0.5 0"/>
                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1379_615"/>
                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1379_615" result="shape"/>
                </filter>
                </defs>
                </svg>

                SVG;
            
            case 'error':
            case 'expired': // 'expired' can share the 'error' icon
                return <<<SVG
                <svg {$svg_attributes}>
                <g filter="url(#filter0_d_115_16)">
                <rect x="4" width="50" height="50" rx="5" fill="#A72A0C"/>
                </g>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.1292 31.7917L27.4221 14.6081C27.6388 14.2511 27.9439 13.9559 28.3079 13.751C28.6719 13.5462 29.0825 13.4386 29.5002 13.4386C29.9179 13.4386 30.3285 13.5462 30.6925 13.751C31.0565 13.9559 31.3616 14.2511 31.5783 14.6081L41.8712 31.7917C42.0833 32.1592 42.1955 32.5759 42.1966 33.0002C42.1977 33.4246 42.0877 33.8418 41.8776 34.2105C41.6675 34.5791 41.3645 34.8864 40.9988 35.1016C40.6331 35.3169 40.2174 35.4327 39.7931 35.4375H19.2073C18.7828 35.4331 18.3669 35.3176 18.0009 35.1024C17.635 34.8873 17.3318 34.58 17.1216 34.2112C16.9113 33.8424 16.8014 33.4249 16.8027 33.0004C16.8041 32.5759 16.9166 32.1591 17.1292 31.7917ZM29.5002 20.125C29.887 20.125 30.2579 20.2786 30.5314 20.5521C30.8049 20.8256 30.9585 21.1966 30.9585 21.5833V25.9583C30.9585 26.3451 30.8049 26.716 30.5314 26.9895C30.2579 27.263 29.887 27.4167 29.5002 27.4167C29.1134 27.4167 28.7425 27.263 28.469 26.9895C28.1955 26.716 28.0419 26.3451 28.0419 25.9583V21.5833C28.0419 21.1966 28.1955 20.8256 28.469 20.5521C28.7425 20.2786 29.1134 20.125 29.5002 20.125ZM28.0419 30.3333C28.0419 29.9466 28.1955 29.5756 28.469 29.3021C28.7425 29.0286 29.1134 28.875 29.5002 28.875H29.5119C29.8986 28.875 30.2696 29.0286 30.5431 29.3021C30.8166 29.5756 30.9702 29.9466 30.9702 30.3333C30.9702 30.7201 30.8166 31.091 30.5431 31.3645C30.2696 31.638 29.8986 31.7917 29.5119 31.7917H29.5002C29.1134 31.7917 28.7425 31.638 28.469 31.3645C28.1955 31.091 28.0419 30.7201 28.0419 30.3333Z" fill="#F5F5F5"/>
                <defs>
                <filter id="filter0_d_115_16" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                <feOffset dy="4"/>
                <feGaussianBlur stdDeviation="2"/>
                <feComposite in2="hardAlpha" operator="out"/>
                <feColorMatrix type="matrix" values="0 0 0 0 0.654902 0 0 0 0 0.164706 0 0 0 0 0.0470588 0 0 0 0.5 0"/>
                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_115_16"/>
                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_115_16" result="shape"/>
                </filter>
                </defs>
                </svg>                
                SVG;

            case 'warning':
                return <<<SVG
                <svg {$svg_attributes}><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                SVG;

            case 'info':
                return <<<SVG
                <svg {$svg_attributes}>
                <g filter="url(#filter0_d_1379_616)">
                <rect x="4" width="50" height="50" rx="5" fill="#145494"/>
                </g>
                <path d="M42.0831 30.3317C41.9696 30.195 41.8582 30.0583 41.7488 29.9264C40.2449 28.1073 39.3351 27.0095 39.3351 21.86C39.3351 19.1939 38.6973 17.0064 37.4401 15.3658C36.5132 14.1538 35.2602 13.2344 33.6086 12.5549C33.5873 12.5431 33.5684 12.5276 33.5525 12.5091C32.9585 10.5198 31.3329 9.1875 29.4995 9.1875C27.6661 9.1875 26.0412 10.5198 25.4472 12.507C25.4314 12.5249 25.4126 12.5399 25.3918 12.5515C21.5377 14.1381 19.6647 17.1821 19.6647 21.8579C19.6647 27.0095 18.7562 28.1073 17.2509 29.9243C17.1415 30.0562 17.0301 30.1902 16.9166 30.3297C16.6235 30.6832 16.4378 31.1133 16.3814 31.569C16.3251 32.0248 16.4005 32.4871 16.5987 32.9014C17.0205 33.79 17.9194 34.3417 18.9455 34.3417H40.061C41.0823 34.3417 41.9751 33.7907 42.3983 32.9062C42.5973 32.4918 42.6735 32.0291 42.6176 31.5728C42.5618 31.1165 42.3763 30.6858 42.0831 30.3317ZM29.4995 39.8125C30.4873 39.8117 31.4565 39.5436 32.3043 39.0365C33.152 38.5295 33.8468 37.8024 34.3148 36.9325C34.3368 36.8908 34.3477 36.8441 34.3464 36.797C34.345 36.7499 34.3315 36.7039 34.3072 36.6635C34.2828 36.6231 34.2484 36.5897 34.2074 36.5665C34.1663 36.5434 34.1199 36.5312 34.0728 36.5312H24.9276C24.8804 36.5311 24.834 36.5431 24.7928 36.5663C24.7516 36.5894 24.7171 36.6228 24.6927 36.6632C24.6683 36.7036 24.6547 36.7496 24.6534 36.7968C24.652 36.844 24.6629 36.8908 24.685 36.9325C25.1529 37.8023 25.8475 38.5293 26.6952 39.0363C27.5428 39.5434 28.5118 39.8116 29.4995 39.8125Z" fill="#F5F5F5"/>
                <defs>
                <filter id="filter0_d_1379_616" x="0" y="0" width="58" height="58" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                <feOffset dy="4"/>
                <feGaussianBlur stdDeviation="2"/>
                <feComposite in2="hardAlpha" operator="out"/>
                <feColorMatrix type="matrix" values="0 0 0 0 0.0784314 0 0 0 0 0.329412 0 0 0 0 0.580392 0 0 0 0.5 0"/>
                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1379_616"/>
                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1379_616" result="shape"/>
                </filter>
                </defs>
                </svg>
                SVG;

            default: // A default 'info' icon
                return <<<SVG
                <svg {$svg_attributes}><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                SVG;
        }
    }
}


// Check if there are any toast messages in the session
if (!empty($_SESSION['toast_messages']) && is_array($_SESSION['toast_messages'])):
?>

<!-- =============================================================== -->
<!-- =            TOAST COMPONENT (HTML)                           -->
<!-- =============================================================== -->

<?php foreach ($_SESSION['toast_messages'] as $toast): ?>
    <?php
        // NEW: Extract the type (e.g., 'success') from the class ('status-success')
        $toast_type = str_replace('status-', '', $toast['class']);
        $svg_icon = get_toast_svg($toast_type);
    ?>
    <!-- UPDATED HTML structure to include the icon -->
    <div class="status-toast <?php echo htmlspecialchars($toast['class']); ?>">
        <?php echo $svg_icon; // Output the SVG icon ?>
        <span class="toast-message"><?php echo htmlspecialchars($toast['message']); ?></span>
    </div>
<?php endforeach; ?>

<!-- =============================================================== -->
<!-- =                     END TOAST COMPONENT                     = -->
<!-- =============================================================== -->

<?php
    // This part remains the same
    unset($_SESSION['toast_messages']);
endif;
?>