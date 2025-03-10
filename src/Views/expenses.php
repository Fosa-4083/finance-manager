<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ausgaben</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Ausgaben</h1>
    <table>
        <thead>
            <tr>
                <th>Kategorie</th>
                <th>Datum</th>
                <th>Beschreibung</th>
                <th>Betrag</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?= htmlspecialchars($expense->getCategoryId()) ?></td>
                    <td><?= htmlspecialchars($expense->getDate()) ?></td>
                    <td><?= htmlspecialchars($expense->getDescription()) ?></td>
                    <td><?= htmlspecialchars($expense->getValue()) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html> 