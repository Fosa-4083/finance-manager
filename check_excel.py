#!/usr/bin/env python3
import pandas as pd
import sys
from datetime import datetime

# Pfad zur Excel-Datei
excel_file = 'test.xlsx'

try:
    # Excel-Datei lesen
    print(f"Versuche, die Datei {excel_file} zu lesen...")
    df = pd.read_excel(excel_file)
    
    # Ausgabe der Spalten und ersten Zeilen zur Überprüfung
    print("\nSpalten in der Excel-Datei:")
    print(df.columns.tolist())
    
    print("\nErste 10 Zeilen der Daten:")
    print(df.head(10))
    
    # Überprüfen, ob es einen Eintrag für Januar 2010 gibt
    print("\nSuche nach Einträgen für Januar 2010:")
    january_2010 = df[df.iloc[:, 0].apply(lambda x: isinstance(x, pd.Timestamp) and x.year == 2010 and x.month == 1)]
    print(january_2010)
    
    # Überprüfen, ob die erste Zeile der Spaltenname ist
    print("\nÜberprüfen der ersten Zeile (Spaltenname):")
    print(df.columns)
    
    # Überprüfen, ob die erste Zeile der Daten ein Datum ist
    print("\nÜberprüfen der ersten Zeile der Daten:")
    first_row = df.iloc[0]
    print(first_row)
    
    # Alle einzigartigen Jahre und Monate auflisten
    print("\nEinzigartige Jahre und Monate in den Daten:")
    years_months = [(x.year, x.month) for x in df.iloc[:, 0] if isinstance(x, pd.Timestamp)]
    print(sorted(set(years_months)))
    
    # Überprüfen, ob es Lücken in den Monaten gibt
    print("\nÜberprüfen auf Lücken in den Monaten:")
    all_months = [(year, month) for year in range(2010, 2020) for month in range(1, 13)]
    missing_months = [ym for ym in all_months if ym not in years_months]
    print("Fehlende Monate:", missing_months)
    
except Exception as e:
    print(f"Fehler beim Lesen der Excel-Datei: {e}") 