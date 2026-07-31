<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('name', 'like', '%Roshan%')->first();
$j = App\Models\Job::find(34);

$rawScore = $u->matchScore($j);
$comp = $u->compositeScore($j, $rawScore);
$details = $u->matchDetails($j, $rawScore);

echo "User: {$u->name} (id: {$u->id})\n";
echo "Job: {$j->title} (id: {$j->id})\n";
echo "Raw Python matchScore: {$rawScore}\n";
echo "Composite breakdown: " . json_encode($comp) . "\n";
echo "Details faissScore in details: " . json_encode($details['composite']) . "\n";
