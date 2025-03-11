<?php

namespace Controllers;

use PDO;

class DashboardController extends BaseController {
    public function __construct($db = null) {
        // Basisklassen-Konstruktor aufrufen
        parent::__construct($db);
    }

    public function index() {
        // Gesamtsummen für den aktuellen Monat
        $currentMonth = date('Y-m');
        $stmt = $this->db->prepare('
            SELECT 
                COALESCE(SUM(CASE WHEN value < 0 THEN value ELSE 0 END), 0) as total_expenses,
                COALESCE(SUM(CASE WHEN value > 0 THEN value ELSE 0 END), 0) as total_income,
                COALESCE(SUM(value), 0) as balance
            FROM expenses 
            WHERE DATE_FORMAT(date, "%Y-%m") = ?
        ');
        $stmt->execute([$currentMonth]);
        $monthlyTotals = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ausgaben nach Kategorien für den aktuellen Monat
        $stmt = $this->db->prepare('
            SELECT 
                c.id,
                c.name,
                c.color,
                COALESCE(SUM(e.value), 0) as total,
                COUNT(e.id) as count
            FROM categories c
            LEFT JOIN expenses e ON c.id = e.category_id AND DATE_FORMAT(e.date, "%Y-%m") = ?
            GROUP BY c.id
            ORDER BY total ASC
        ');
        $stmt->execute([$currentMonth]);
        $categoryTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Letzte 5 Transaktionen
        $stmt = $this->db->prepare('
            SELECT 
                e.*,
                c.name as category_name,
                c.color as category_color
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            ORDER BY e.date DESC
            LIMIT 5
        ');
        $stmt->execute();
        $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top 5 Ausgaben des aktuellen Monats
        $stmt = $this->db->prepare('
            SELECT 
                e.*,
                c.name as category_name,
                c.color as category_color
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            WHERE DATE_FORMAT(e.date, "%Y-%m") = ? AND e.value < 0
            ORDER BY e.value ASC
            LIMIT 5
        ');
        $stmt->execute([$currentMonth]);
        $topExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Monatliche Ausgaben für die letzten 6 Monate
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthName = date('M Y', strtotime("-$i months"));
            
            $stmt = $this->db->prepare('
                SELECT 
                    COALESCE(SUM(CASE WHEN value < 0 THEN value ELSE 0 END), 0) as expenses,
                    COALESCE(SUM(CASE WHEN value > 0 THEN value ELSE 0 END), 0) as income
                FROM expenses 
                WHERE DATE_FORMAT(date, "%Y-%m") = ?
            ');
            $stmt->execute([$month]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $monthlyData[] = [
                'month' => $monthName,
                'expenses' => abs($result['expenses']),
                'income' => $result['income']
            ];
        }

        // Aktive Projekte
        $stmt = $this->db->query('
            SELECT 
                p.*,
                0 as total_expenses,
                0 as expense_count
            FROM projects p
            WHERE p.status = "aktiv"
            GROUP BY p.id
            ORDER BY p.name');
        $activeProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vergleich zum Vormonat
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $stmt = $this->db->prepare('
            SELECT COALESCE(SUM(CASE WHEN value < 0 THEN value ELSE 0 END), 0) as total_expenses
            FROM expenses 
            WHERE DATE_FORMAT(date, "%Y-%m") = ?
        ');
        $stmt->execute([$lastMonth]);
        $lastMonthTotal = $stmt->fetchColumn();

        // Stelle sicher, dass $lastMonthTotal nicht null ist
        $lastMonthTotal = $lastMonthTotal ?: 0;

        include __DIR__ . '/../Views/dashboard/index.php';
    }
} 