<?php
// Zakazga — отправка заявок в Telegram-группу "Juba.uz zayavkalar" через @Jubamarketing_bot
// Впишите токен бота и ID группы:
$BOT_TOKEN = 'ВСТАВЬТЕ_ТОКЕН_БОТА';
$CHAT_ID   = 'ВСТАВЬТЕ_ID_ГРУППЫ'; // например -1001234567890

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }

$d = json_decode(file_get_contents('php://input'), true);
if (!$d || !empty($d['website'])) { echo json_encode(['ok'=>false]); exit; } // антиспам

$e = fn($s) => htmlspecialchars(trim((string)$s), ENT_QUOTES, 'UTF-8');
$fmt = fn($n) => number_format((int)$n, 0, '', ' ') . ' сум';

$lines = '';
foreach (($d['items'] ?? []) as $it) {
    $lines .= '• ' . $e($it['name']) . ' × ' . (int)$it['qty'] . ' — ' . $fmt($it['price'] * $it['qty']) . "\n";
}

$text  = "🟠 <b>zakazga.uz</b> — заказ фуршетного сета\n\n";
$text .= "👤 " . $e($d['name'] ?? '') . "\n";
$text .= "📞 " . $e($d['phone'] ?? '') . "\n";
$text .= "🗓 " . $e($d['datetime'] ?? '') . "\n";
$text .= "📍 " . $e($d['address'] ?? '') . "\n\n";
$text .= $lines . "\n<b>Итого: " . $fmt($d['total'] ?? 0) . "</b>\n";
if (!empty($d['comment'])) $text .= "\n💬 " . $e($d['comment']);

$ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POSTFIELDS => ['chat_id'=>$CHAT_ID, 'text'=>$text, 'parse_mode'=>'HTML'],
]);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

echo json_encode(['ok' => !empty($res['ok'])]);
