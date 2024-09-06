<?php
define("ADMIN_SECTION", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>OpenApi</title>
    <link rel="stylesheet" type="text/css" href="/swagger/swagger-ui.css" />
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="/swagger/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            console.log(window.location.pathname);
            const ui = SwaggerUIBundle({
                url: "/api/v1/openapi",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout',
            });
            // End Swagger UI call region
            window.ui = ui;
        };
    </script>
</body>

</html>