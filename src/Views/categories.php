<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategorien</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Kategorien</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Beschreibung</th>
                <th>Farbe</th>
                <th>Ziel</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category->getName()) ?></td>
                    <td><?= htmlspecialchars($category->getDescription()) ?></td>
                    <td style="background-color: <?= htmlspecialchars($category->getColor()) ?>;"></td>
                    <td><?= htmlspecialchars($category->getGoal()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 