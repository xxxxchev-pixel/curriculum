-- ATUALIZAÇÃO DE PREÇOS COMPETITIVOS - DEZEMBRO 2024
-- Baseado em pesquisa de mercado (Worten, FNAC, Media Markt, Amazon)
-- Preços GomesTech: 5-10% abaixo do mercado

-- ==================== SMARTPHONES ====================
-- iPhone 13 128GB: Mercado €519-549 → GomesTech €489
UPDATE produtos SET preco = 489.00 WHERE modelo = 'iPhone 13 128GB' AND marca = 'Apple';

-- iPhone 14 128GB: Mercado €679-729 → GomesTech €639
UPDATE produtos SET preco = 639.00 WHERE modelo = 'iPhone 14 128GB' AND marca = 'Apple';

-- iPhone 15 128GB: Mercado €879-949 → GomesTech €829
UPDATE produtos SET preco = 829.00 WHERE modelo = 'iPhone 15 128GB' AND marca = 'Apple';

-- iPhone 15 Pro 256GB: Mercado €1249-1349 → GomesTech €1179
UPDATE produtos SET preco = 1179.00 WHERE modelo = 'iPhone 15 Pro 256GB' AND marca = 'Apple';

-- Samsung Galaxy S23: Mercado €649-699 → GomesTech €599
UPDATE produtos SET preco = 599.00 WHERE modelo = 'Samsung Galaxy S23' AND marca = 'Samsung';

-- Samsung Galaxy S24: Mercado €849-899 → GomesTech €799
UPDATE produtos SET preco = 799.00 WHERE modelo = 'Samsung Galaxy S24' AND marca = 'Samsung';

-- Samsung Galaxy A54: Mercado €399-449 → GomesTech €369
UPDATE produtos SET preco = 369.00 WHERE modelo = 'Samsung Galaxy A54' AND marca = 'Samsung';

-- Samsung Galaxy A34: Mercado €329-369 → GomesTech €299
UPDATE produtos SET preco = 299.00 WHERE modelo = 'Samsung Galaxy A34' AND marca = 'Samsung';

-- Xiaomi 13 Pro: Mercado €899-999 → GomesTech €849
UPDATE produtos SET preco = 849.00 WHERE modelo = 'Xiaomi 13 Pro' AND marca = 'Xiaomi';

-- Xiaomi Redmi Note 13 Pro: Mercado €329-369 → GomesTech €299
UPDATE produtos SET preco = 299.00 WHERE modelo = 'Xiaomi Redmi Note 13 Pro' AND marca = 'Xiaomi';

-- OnePlus 11: Mercado €729-799 → GomesTech €679
UPDATE produtos SET preco = 679.00 WHERE modelo = 'OnePlus 11' AND marca = 'OnePlus';

-- Google Pixel 8: Mercado €749-799 → GomesTech €699
UPDATE produtos SET preco = 699.00 WHERE modelo = 'Google Pixel 8' AND marca = 'Google';


-- ==================== LAPTOPS ====================
-- MacBook Air M2: Mercado €1199-1299 → GomesTech €1129
UPDATE produtos SET preco = 1129.00 WHERE modelo = 'MacBook Air M2' AND marca = 'Apple';

-- MacBook Pro 14" M3: Mercado €2099-2299 → GomesTech €1979
UPDATE produtos SET preco = 1979.00 WHERE modelo LIKE 'MacBook Pro 14%M3%' AND marca = 'Apple';

-- Dell XPS 13: Mercado €1099-1199 → GomesTech €1029
UPDATE produtos SET preco = 1029.00 WHERE modelo = 'Dell XPS 13' AND marca = 'Dell';

-- HP Pavilion 15: Mercado €699-799 → GomesTech €649
UPDATE produtos SET preco = 649.00 WHERE modelo = 'HP Pavilion 15' AND marca = 'HP';

-- Lenovo ThinkPad X1: Mercado €1399-1549 → GomesTech €1299
UPDATE produtos SET preco = 1299.00 WHERE modelo = 'Lenovo ThinkPad X1 Carbon' AND marca = 'Lenovo';

-- ASUS ZenBook 14: Mercado €899-999 → GomesTech €839
UPDATE produtos SET preco = 839.00 WHERE modelo = 'ASUS ZenBook 14' AND marca = 'ASUS';

-- MSI Gaming GF63: Mercado €799-899 → GomesTech €749
UPDATE produtos SET preco = 749.00 WHERE modelo = 'MSI GF63 Thin' AND marca = 'MSI';


-- ==================== TABLETS ====================
-- iPad Air M2: Mercado €699-769 → GomesTech €649
UPDATE produtos SET preco = 649.00 WHERE modelo = 'iPad Air M2' AND marca = 'Apple';

-- iPad 10.9": Mercado €449-499 → GomesTech €419
UPDATE produtos SET preco = 419.00 WHERE modelo LIKE 'iPad 10.9%' AND marca = 'Apple';

-- Samsung Galaxy Tab S9: Mercado €799-899 → GomesTech €749
UPDATE produtos SET preco = 749.00 WHERE modelo = 'Samsung Galaxy Tab S9' AND marca = 'Samsung';

-- Samsung Galaxy Tab A9: Mercado €199-249 → GomesTech €179
UPDATE produtos SET preco = 179.00 WHERE modelo = 'Samsung Galaxy Tab A9' AND marca = 'Samsung';

-- Lenovo Tab P11: Mercado €249-299 → GomesTech €229
UPDATE produtos SET preco = 229.00 WHERE modelo = 'Lenovo Tab P11' AND marca = 'Lenovo';


-- ==================== TVS ====================
-- Samsung QLED 55": Mercado €899-999 → GomesTech €839
UPDATE produtos SET preco = 839.00 WHERE modelo LIKE 'Samsung QLED 55%' AND marca = 'Samsung';

-- LG OLED 55": Mercado €1299-1449 → GomesTech €1199
UPDATE produtos SET preco = 1199.00 WHERE modelo LIKE 'LG OLED 55%' AND marca = 'LG';

-- Sony Bravia 65": Mercado €1499-1699 → GomesTech €1399
UPDATE produtos SET preco = 1399.00 WHERE modelo LIKE 'Sony Bravia 65%' AND marca = 'Sony';

-- Philips 43" 4K: Mercado €399-449 → GomesTech €369
UPDATE produtos SET preco = 369.00 WHERE modelo LIKE 'Philips%43%4K%' AND marca = 'Philips';

-- TCL 50" QLED: Mercado €499-549 → GomesTech €459
UPDATE produtos SET preco = 459.00 WHERE modelo LIKE 'TCL%50%QLED%' AND marca = 'TCL';


-- ==================== WEARABLES ====================
-- Apple Watch Series 9: Mercado €449-499 → GomesTech €419
UPDATE produtos SET preco = 419.00 WHERE modelo = 'Apple Watch Series 9' AND marca = 'Apple';

-- Apple Watch SE: Mercado €299-349 → GomesTech €279
UPDATE produtos SET preco = 279.00 WHERE modelo = 'Apple Watch SE' AND marca = 'Apple';

-- Samsung Galaxy Watch 6: Mercado €349-399 → GomesTech €319
UPDATE produtos SET preco = 319.00 WHERE modelo = 'Samsung Galaxy Watch 6' AND marca = 'Samsung';

-- Garmin Forerunner 265: Mercado €449-499 → GomesTech €419
UPDATE produtos SET preco = 419.00 WHERE modelo = 'Garmin Forerunner 265' AND marca = 'Garmin';

-- Fitbit Charge 6: Mercado €159-179 → GomesTech €149
UPDATE produtos SET preco = 149.00 WHERE modelo = 'Fitbit Charge 6' AND marca = 'Fitbit';


-- ==================== ÁUDIO ====================
-- Sony WH-1000XM5: Mercado €379-429 → GomesTech €349
UPDATE produtos SET preco = 349.00 WHERE modelo = 'Sony WH-1000XM5' AND marca = 'Sony';

-- AirPods Pro 2: Mercado €279-299 → GomesTech €259
UPDATE produtos SET preco = 259.00 WHERE modelo = 'AirPods Pro 2' AND marca = 'Apple';

-- Bose QuietComfort 45: Mercado €329-369 → GomesTech €299
UPDATE produtos SET preco = 299.00 WHERE modelo = 'Bose QuietComfort 45' AND marca = 'Bose';

-- JBL Flip 6: Mercado €119-139 → GomesTech €109
UPDATE produtos SET preco = 109.00 WHERE modelo = 'JBL Flip 6' AND marca = 'JBL';

-- Marshall Emberton II: Mercado €149-169 → GomesTech €139
UPDATE produtos SET preco = 139.00 WHERE modelo = 'Marshall Emberton II' AND marca = 'Marshall';


-- ==================== CONSOLAS ====================
-- PlayStation 5: Mercado €549-599 → GomesTech €519
UPDATE produtos SET preco = 519.00 WHERE modelo LIKE 'PlayStation 5%' AND marca = 'Sony';

-- Xbox Series X: Mercado €499-549 → GomesTech €479
UPDATE produtos SET preco = 479.00 WHERE modelo = 'Xbox Series X' AND marca = 'Microsoft';

-- Nintendo Switch OLED: Mercado €349-369 → GomesTech €329
UPDATE produtos SET preco = 329.00 WHERE modelo = 'Nintendo Switch OLED' AND marca = 'Nintendo';

-- Steam Deck: Mercado €419-469 → GomesTech €399
UPDATE produtos SET preco = 399.00 WHERE modelo = 'Steam Deck' AND marca = 'Valve';


-- ==================== AR CONDICIONADO ====================
-- Daikin Comfora 12000 BTU: Mercado €699-799 → GomesTech €649
UPDATE produtos SET preco = 649.00 WHERE modelo LIKE 'Daikin Comfora%12000%' AND marca = 'Daikin';

-- Mitsubishi MSZ-HR: Mercado €799-899 → GomesTech €749
UPDATE produtos SET preco = 749.00 WHERE modelo LIKE 'Mitsubishi MSZ-HR%' AND marca = 'Mitsubishi';

-- LG Dual Cool: Mercado €599-699 → GomesTech €559
UPDATE produtos SET preco = 559.00 WHERE modelo LIKE 'LG Dual Cool%' AND marca = 'LG';

-- Samsung WindFree: Mercado €899-999 → GomesTech €849
UPDATE produtos SET preco = 849.00 WHERE modelo LIKE 'Samsung WindFree%' AND marca = 'Samsung';


-- ==================== ASPIRADORES ====================
-- Dyson V15: Mercado €649-729 → GomesTech €599
UPDATE produtos SET preco = 599.00 WHERE modelo = 'Dyson V15 Detect' AND marca = 'Dyson';

-- Roomba j7+: Mercado €799-899 → GomesTech €749
UPDATE produtos SET preco = 749.00 WHERE modelo = 'iRobot Roomba j7+' AND marca = 'iRobot';

-- Xiaomi Robot Vacuum S10: Mercado €399-449 → GomesTech €369
UPDATE produtos SET preco = 369.00 WHERE modelo LIKE 'Xiaomi Robot Vacuum S10%' AND marca = 'Xiaomi';

-- Rowenta X-Force: Mercado €299-349 → GomesTech €279
UPDATE produtos SET preco = 279.00 WHERE modelo LIKE 'Rowenta X-Force%' AND marca = 'Rowenta';


-- ==================== FRIGORÍFICOS ====================
-- Samsung Family Hub: Mercado €2499-2799 → GomesTech €2299
UPDATE produtos SET preco = 2299.00 WHERE modelo LIKE 'Samsung Family Hub%' AND marca = 'Samsung';

-- LG InstaView: Mercado €1999-2299 → GomesTech €1849
UPDATE produtos SET preco = 1849.00 WHERE modelo LIKE 'LG InstaView%' AND marca = 'LG';

-- Bosch Serie 6: Mercado €1299-1449 → GomesTech €1199
UPDATE produtos SET preco = 1199.00 WHERE modelo LIKE 'Bosch Serie 6%' AND marca = 'Bosch';

-- Whirlpool W Collection: Mercado €899-999 → GomesTech €839
UPDATE produtos SET preco = 839.00 WHERE modelo LIKE 'Whirlpool W Collection%' AND marca = 'Whirlpool';


-- ==================== MÁQUINAS DE LAVAR ====================
-- Bosch Serie 8: Mercado €899-999 → GomesTech €839
UPDATE produtos SET preco = 839.00 WHERE modelo LIKE 'Bosch Serie 8%Roupa%' AND marca = 'Bosch';

-- LG AI DD: Mercado €749-849 → GomesTech €699
UPDATE produtos SET preco = 699.00 WHERE modelo LIKE 'LG AI DD%' AND marca = 'LG';

-- Samsung EcoBubble: Mercado €599-699 → GomesTech €559
UPDATE produtos SET preco = 559.00 WHERE modelo LIKE 'Samsung EcoBubble%' AND marca = 'Samsung';

-- Candy Smart: Mercado €449-499 → GomesTech €419
UPDATE produtos SET preco = 419.00 WHERE modelo LIKE 'Candy Smart%' AND marca = 'Candy';


-- ==================== MICRO-ONDAS ====================
-- Samsung Smart Oven: Mercado €299-349 → GomesTech €279
UPDATE produtos SET preco = 279.00 WHERE modelo LIKE 'Samsung Smart Oven%' AND marca = 'Samsung';

-- Panasonic Inverter: Mercado €249-299 → GomesTech €229
UPDATE produtos SET preco = 229.00 WHERE modelo LIKE 'Panasonic%Inverter%' AND marca = 'Panasonic';

-- LG NeoChef: Mercado €199-249 → GomesTech €179
UPDATE produtos SET preco = 179.00 WHERE modelo LIKE 'LG NeoChef%' AND marca = 'LG';

-- Whirlpool JetChef: Mercado €399-449 → GomesTech €369
UPDATE produtos SET preco = 369.00 WHERE modelo LIKE 'Whirlpool JetChef%' AND marca = 'Whirlpool';


-- ==================== MÁQUINAS DE CAFÉ ====================
-- De'Longhi Magnifica S: Mercado €449-499 → GomesTech €419
UPDATE produtos SET preco = 419.00 WHERE modelo LIKE "De'Longhi Magnifica S%" AND marca = "De'Longhi";

-- Nespresso Vertuo: Mercado €199-249 → GomesTech €179
UPDATE produtos SET preco = 179.00 WHERE modelo LIKE 'Nespresso Vertuo%' AND marca = 'Nespresso';

-- Philips LatteGo: Mercado €599-699 → GomesTech €559
UPDATE produtos SET preco = 559.00 WHERE modelo LIKE 'Philips%LatteGo%' AND marca = 'Philips';

-- Krups Espresseria: Mercado €349-399 → GomesTech €319
UPDATE produtos SET preco = 319.00 WHERE modelo LIKE 'Krups Espresseria%' AND marca = 'Krups';


-- Verificar produtos atualizados
SELECT marca, modelo, preco, preco_original 
FROM produtos 
WHERE preco < preco_original
ORDER BY categoria, marca, preco DESC
LIMIT 50;
