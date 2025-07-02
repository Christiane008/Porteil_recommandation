<?php
$courses = [
    ["id" => 1, "name" => "Maths", "description" => "Cours de mathématiques générales", "teacher_id" => 1],
    ["id" => 2, "name" => "Algorithmique", "description" => "Bases des algorithmes", "teacher_id" => 2],
    ["id" => 3, "name" => "Java", "description" => "Programmation orientée objet avec Java", "teacher_id" => 5],
    ["id" => 4, "name" => "Merise", "description" => "Modélisation des données", "teacher_id" => 2],
    ["id" => 5, "name" => "Logique", "description" => "Logique mathématique et informatique", "teacher_id" => 3]
];

$teachers = [
    ["id" => 1, "name" => "Mr Romano"],
    ["id" => 2, "name" => "Mr Tojo"],
    ["id" => 3, "name" => "Mr Donatien"],
    ["id" => 4, "name" => "Mr Fabrice"],
    ["id" => 5, "name" => "Mr Tsinjo"]
];

$reviews = [];
if (file_exists('reviews.json')) {
    $reviews = json_decode(file_get_contents('reviews.json'), true);
}

function stars($avg) {
    $filled = floor($avg);
    $half = ($avg - $filled) >= 0.5 ? 1 : 0;
    $empty = 5 - $filled - $half;
    return str_repeat('★', $filled)
         . ($half ? '⯨' : '')
         . str_repeat('☆', $empty);
}

function courseStats($courseId, $reviews) {
    $rs = array_filter($reviews, fn($r) => $r['type']==='cours' && $r['id']==$courseId);
    $count = count($rs);
    $sum = array_sum(array_column($rs, 'note'));
    $avg = $count ? $sum/$count : 0;
    $top = $rs ? reset($rs)['avis'] : 'Aucun avis.';
    return [$avg, $count, $top];
}

function teacherAvg($teacherId, $courses, $reviews) {
    $relevantCourses = array_filter($courses, fn($c) => $c['teacher_id'] == $teacherId);
    $totalNotes = 0;
    $weightedSum = 0;
    foreach ($relevantCourses as $c) {
        [$avg, $count] = courseStats($c['id'], $reviews);
        $totalNotes += $count;
        $weightedSum += $avg * $count;
    }
    if ($totalNotes < 3) return [null, $totalNotes];
    return [$weightedSum / $totalNotes, $totalNotes];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Note et Avis</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            margin: 0;
            background: #f3f0f9;
            color: #333;
        }
        h1, h2 {
            text-align: center;
            color: #4b3869;
        }
        .scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 20px 0;
            margin-bottom: 40px;
        }
        .scroll-container::-webkit-scrollbar {
            display: none;
        }
        .scroll-item {
            flex: 0 0 auto;
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            width: 240px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            transition: transform 0.2s ease;
        }
        .scroll-item:hover {
            transform: translateY(-4px);
        }
        .scroll-item h3 {
            font-size: 18px;
            margin-bottom: 6px;
        }
        .scroll-item p {
            font-size: 14px;
            margin: 4px 0;
        }
        .review-stars {
            color: gold;
            font-size: 1.3rem;
            margin-bottom: 4px;
        }
        .comment-preview {
            font-style: italic;
            color: #666;
            font-size: 13px;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        }
        form label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }
        form select, form textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            resize: vertical;
        }
        .rating {
            direction: rtl;
            unicode-bidi: bidi-override;
            display: inline-flex;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .rating input {
            display: none;
        }
        .rating label {
            color: #ccc;
            cursor: pointer;
            transition: color .2s, transform .2s;
        }
        .rating label:hover,
        .rating label:hover ~ label {
            color: orange;
            transform: scale(1.1);
        }
        .rating input:checked ~ label {
            color: gold;
        }
        button {
            background-color: #8e44ad;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease;
        }
        button:hover {
            background-color: #9b59b6;
        }
    </style>
</head>
<body>

<h1>Note et Avis</h1>

<h2>Cours populaires</h2>
<div class="scroll-container">
<?php foreach ($courses as $c): 
    [$avg, $count, $top] = courseStats($c['id'], $reviews);
?>
    <div class="scroll-item">
        <h3><?=htmlspecialchars($c['name'])?></h3>
        <div class="review-stars"><?= stars($avg) ?></div>
        <p class="comment-preview">"<?= htmlspecialchars($top) ?>"</p>
    </div>
<?php endforeach; ?>
</div>

<h2>Enseignants populaires</h2>
<div class="scroll-container">
<?php foreach ($teachers as $t):
    [$tavg, $totalNotes] = teacherAvg($t['id'], $courses, $reviews);
?>
    <div class="scroll-item">
        <h3><?=htmlspecialchars($t['name'])?></h3>
        <div class="review-stars">
            <?= $tavg === null ? 'Pas assez de notes' : stars($tavg) ?>
        </div>
        <?php if ($tavg !== null): ?>
        <p class="comment-preview">
            <?= $totalNotes ?> avis
        </p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<h2>Donner un avis</h2>
<form action="" method="post">
    <label for="cours">Cours</label>
    <select name="cours" id="cours" required>
        <option value="">Choisissez un cours</option>
        <?php foreach($courses as $co): ?>
        <option value="<?=$co['id']?>"><?=$co['name']?></option>
        <?php endforeach;?>
    </select>

    <label>Note</label>
    <div class="rating">
        <input type="radio" id="star5" name="note" value="5" required><label for="star5">★</label>
        <input type="radio" id="star4" name="note" value="4"><label for="star4">★</label>
        <input type="radio" id="star3" name="note" value="3"><label for="star3">★</label>
        <input type="radio" id="star2" name="note" value="2"><label for="star2">★</label>
        <input type="radio" id="star1" name="note" value="1"><label for="star1">★</label>
    </div>

    <label for="avis">Avis</label>
    <textarea name="avis" id="avis" rows="4" required></textarea>

    <button type="submit">Envoyer</button>
</form>

<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
    $c = $_POST['cours'];
    $n = intval($_POST['note']);
    $a = trim($_POST['avis']);
    if($c && $n && $a){
        $reviews[] = ['type'=>'cours','id'=>$c,'note'=>$n,'avis'=>$a];
        file_put_contents('reviews.json', json_encode($reviews, JSON_PRETTY_PRINT));
        header('Location:'.$_SERVER['PHP_SELF']); exit;
    }
}
?>
</body>
</html>