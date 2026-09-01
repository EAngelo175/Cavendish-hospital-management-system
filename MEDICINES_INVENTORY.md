# Hospital Pharmacy Medicines Inventory

## Medicines Added to the System

### Pain Relief & Anti-inflammatory
1. **Paracetamol 500mg** - UGX 500 (Qty: 150)
2. **Ibuprofen 400mg** - UGX 800 (Qty: 120)
3. **Aspirin 100mg** - UGX 600 (Qty: 100)
4. **Diclofenac 50mg** - UGX 1,600 (Qty: 65)

### Antibiotics
5. **Amoxicillin 500mg** - UGX 2,000 (Qty: 80)
6. **Erythromycin 250mg** - UGX 1,800 (Qty: 60)
7. **Metronidazole 400mg** - UGX 1,400 (Qty: 55)
8. **Ciprofloxacin 500mg** - UGX 3,500 (Qty: 50)
9. **Doxycycline 100mg** - UGX 2,800 (Qty: 60)

### Antifungal & Antiviral
10. **Fluconazole 150mg** - UGX 4,500 (Qty: 40)
11. **Acyclovir 400mg** - UGX 5,000 (Qty: 35)

### Blood Pressure & Cardiac
12. **Lisinopril 10mg** - UGX 2,500 (Qty: 75)
13. **Atenolol 50mg** - UGX 2,200 (Qty: 90)
14. **Amlodipine 5mg** - UGX 2,800 (Qty: 80)

### Diabetes
15. **Metformin 500mg** - UGX 1,200 (Qty: 100)

### Gastrointestinal
16. **Omeprazole 20mg** - UGX 1,500 (Qty: 70)
17. **Ranitidine 150mg** - UGX 1,300 (Qty: 85)

### Respiratory & Cough
18. **Cough Syrup 100ml** - UGX 3,000 (Qty: 110)
19. **Salbutamol Inhaler** - UGX 6,500 (Qty: 30)

### Allergy & Antihistamine
20. **Antihistamine Tablet** - UGX 1,000 (Qty: 95)
21. **Antihistamine Syrup 100ml** - UGX 3,500 (Qty: 80)

### Malaria & Antimalarial
22. **Chloroquine 250mg** - UGX 1,800 (Qty: 100)
23. **Artemether 80mg** - UGX 8,000 (Qty: 50)

### Supplements & Vitamins
24. **Multivitamin Tablet** - UGX 2,000 (Qty: 130)
25. **Vitamin C 500mg** - UGX 800 (Qty: 140)
26. **Iron Supplement** - UGX 1,100 (Qty: 70)
27. **Glucose Solution 100ml** - UGX 1,500 (Qty: 120)

### Topical & Creams
28. **Antiseptic Cream** - UGX 2,500 (Qty: 100)
29. **Hydrocortisone Cream** - UGX 3,000 (Qty: 45)
30. **Antibiotic Ointment** - UGX 2,800 (Qty: 70)

### Steroids
31. **Prednisone 5mg** - UGX 2,200 (Qty: 60)

## Price Range Summary
- **Cheapest:** Paracetamol - UGX 500
- **Most Expensive:** Artemether - UGX 8,000
- **Average Price:** ~UGX 2,500

## Total Inventory Value
Estimated total stock value: ~UGX 800,000+

## Categories Included
- Pain Relief
- Anti-inflammatory
- Antibiotics
- Antifungal
- Antiviral
- Blood Pressure
- Diabetes
- Gastrointestinal
- Respiratory
- Allergy
- Antimalarial
- Supplements
- Topical
- Steroids

## Expiry Dates
- Most medicines: 2 years from today
- Artemether, Acyclovir, Ciprofloxacin, Doxycycline, Cough Syrup: 1 year

## How to Import These Medicines

### Option 1: Run SQL Migration
1. Go to your MySQL client or phpMyAdmin
2. Open `database/hospital.sql`
3. Run the SQL queries to import all medicines

### Option 2: Manual Addition via Pharmacy Interface
1. Log in as Pharmacist
2. Go to Pharmacy → Medicine Inventory
3. Click "Add medicine"
4. Enter each medicine details manually

### Option 3: Direct Database Insert
```sql
-- Run this SQL directly in your database
INSERT INTO medicines (name, category, quantity, price, expiry_date) VALUES
('Paracetamol 500mg', 'Pain Relief', 150, 500, '2028-09-01'),
... [see hospital.sql for full list]
```

## Managing Medicines

### Edit Medicines
- Click "Edit" button next to any medicine
- Update quantity, price, expiry date as needed

### Delete Medicines
- Click "Delete" button next to any medicine
- Confirm the deletion

### Low Stock Alerts
- Medicines with ≤ 10 units show on Pharmacist Dashboard
- Yellow alert panel shows items needing reorder

## Usage Notes
- All prices are in UGX (Ugandan Shilling)
- Quantities can be adjusted based on actual inventory
- Expiry dates can be updated from medicine packaging
- Categories help organize and find medicines quickly
