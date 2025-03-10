<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ausgabenziele</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Ausgabenziele</h1>
    <table>
        <thead>
            <tr>
                <th>Kategorie</th>
                <th>Jahr</th>
                <th>Ziel</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenseGoals as $goal): ?>
                <tr>
                    <td><?= htmlspecialchars($goal->getCategoryId()) ?></td>
                    <td><?= htmlspecialchars($goal->getYear()) ?></td>
                    <td><?= htmlspecialchars($goal->getGoal()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 