<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $camIP = "172.20.10.2";
    $serverIP = "172.20.10.14";

    $imageName = "capture_" . date("Ymd_His") . ".jpg";
    $uploadServer = "http://$serverIP/upload_image.php";

    $pythonPath = "C:\\Users\\KEVIN\\Desktop\\Lector_Matriculas\\venv\\Scripts\\python.exe";
    $scriptPath = "C:\\Users\\KEVIN\\Desktop\\Lector_Matriculas\\ANPR.py";
    $imagePath = "C:\\xampp\\htdocs\\uploads\\" . $imageName;

    // =========================
    // 1) CAPTURA
    // =========================
    $url = "http://$camIP/capture?name=$imageName&server=$uploadServer";
    $response = @file_get_contents($url);

    if ($response === false) {
        echo "ERROR_CAM";
        exit;
    }

    // =========================
    // 2) ESPERAR ARCHIVO
    // =========================
    $timeout = 5;
    $start = time();

    while (!file_exists($imagePath)) {
        if ((time() - $start) > $timeout) {
            echo "ERROR_UPLOAD";
            exit;
        }
        usleep(300000);
    }

    // Esperar escritura completa
    clearstatcache();
    usleep(500000);

    // =========================
    // 3) VALIDAR IMAGEN
    // =========================
    if (!file_exists($imagePath) || filesize($imagePath) < 5000) {
        echo "ERROR_IMAGE";
        exit;
    }

    // =========================
    // 4) OCR
    // =========================
    $command = "\"$pythonPath\" \"$scriptPath\" \"$imagePath\" 2>&1";

    $output = [];
    $return_var = 0;

    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        file_put_contents(
            "log.txt",
            date("H:i:s") . " - OCR ERROR: " . implode(" ", $output) . "\n",
            FILE_APPEND
        );
        echo "ERROR_OCR";
        exit;
    }

    $plate = implode("", $output);

    // =========================
    // 5) LIMPIEZA
    // =========================
    $plate = strtoupper(trim($plate));
    $plate = preg_replace('/[^A-Z0-9]/', '', $plate);

    // Longitud mínima razonable
    if (strlen($plate) < 5) {
        $plate = "";
    }

    // =========================
    // 6) LOG
    // =========================
    file_put_contents(
        "log.txt",
        date("H:i:s") . " - " . ($plate ?: "NO_PLATE") . "\n",
        FILE_APPEND
    );

    // =========================
    // 7) RESPUESTA
    // =========================
    echo $plate ?: "NO_PLATE";
}
?>
