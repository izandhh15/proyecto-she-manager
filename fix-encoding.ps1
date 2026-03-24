$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

$replacements = [ordered]@{
    'Ã¡'='á'; 'Ã©'='é'; 'Ã­'='í'; 'Ã³'='ó'; 'Ãº'='ú'
    'Ã'='Á'; 'Ã‰'='É'; 'Ã'='Í'; 'Ã“'='Ó'; 'Ãš'='Ú'
    'Ã±'='ñ'; 'Ã‘'='Ñ'
    'Ã¼'='ü'; 'Ãœ'='Ü'
    'Ã '`='à'; 'Ã¨'='è'; 'Ã²'='ò'; 'Ã€'='À'; 'Ãˆ'='È'; 'Ã’'='Ò'
    'Ã¤'='ä'; 'Ã¶'='ö'; 'Ã„'='Ä'; 'Ã–'='Ö'
    'Ã§'='ç'; 'Ã‡'='Ç'
    'Â¿'='¿'; 'Â¡'='¡'; 'Â·'='·'
    'â€”'='—'; 'â€“'='–'; 'â€˜'='‘'; 'â€™'='’'; 'â€œ'='“'; 'â€'='”'
    'â€¦'='…'; 'â€¢'='•'; 'â„¢'='™'; 'â‚¬'='€'
    'â†’'='→'; 'â†'='←'; 'â†”'='↔'
    'âœ“'='✓'; 'âœ”'='✔'; 'âœ•'='✕'
    'Premi?re'='Première'
    'G?rard'='Gérard'
    'Oc?ane'='Océane'
    'Fran?ois'='François'
    'Ke?ta'='Keïta'
}

$files = Get-ChildItem resources,app,config,database,data -Recurse -File | Where-Object {
    $_.Extension -in '.php','.blade.php','.css','.js','.json','.md','.txt','.env','.example'
}

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $original = $content

    foreach ($key in $replacements.Keys) {
        $content = $content.Replace($key, $replacements[$key])
    }

    if ($content -ne $original) {
        [System.IO.File]::WriteAllText($file.FullName, $content, $utf8NoBom)
        Write-Host "Corregido: $($file.FullName)"
    }
}

php artisan optimize:clear
npm run build