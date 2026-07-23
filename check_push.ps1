$files = Get-ChildItem -Path 'resources\views' -Recurse -Filter '*.blade.php'
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw
    $p = ([regex]::Matches($content, '@push\(')).Count
    $e = ([regex]::Matches($content, '@endpush')).Count
    if ($p -ne $e) {
        Write-Host "MISMATCH in $($f.FullName): @push = $p, @endpush = $e"
    }
}
