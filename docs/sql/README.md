# Bases de datos y dumps locales

Los dumps completos (`*.sql`) **no se publican en el repositorio**: contienen
datos de negocios, correos y hashes de contraseñas. Permanecen solo en tu PC y
están excluidos por `.gitignore`.

Este documento describe el modelo de datos y cómo generar y restaurar los dumps
localmente.

---

## 1. Bases de datos del sistema

| Base | Uso | Tablas |
|---|---|---|
| `tenancy` | Panel central: clientes, planes, módulos, dominios | 20 |
| `tenancy_<subdominio>` | Una base por empresa (tenant) | 259 |

Las tres instalaciones de ejemplo son `tenancy_sistemaventa`,
`tenancy_hotelprueba` y `tenancy_econocomercio`.

---

## 2. Generar los dumps en tu PC

Con MariaDB en marcha y el `.env` configurado:

```powershell
.\scripts\generar-dumps.ps1
```

El script:

1. Lee `DB_HOST`, `DB_PORT`, `DB_USERNAME` y `DB_PASSWORD` desde `.env`.
2. Localiza `mysqldump` en el PATH o en las rutas habituales de MariaDB/XAMPP.
3. Exporta `tenancy` como `01-sistema-tenancy.sql`.
4. Exporta cada base `tenancy_*` como `02-tenant-<nombre>.sql`, `03-...`, etc.
5. Guarda todo en `docs/sql/` (ignorado por Git).

Archivos esperados:

| Archivo | Base de datos |
|---|---|
| `01-sistema-tenancy.sql` | `tenancy` |
| `02-tenant-sistemaventa.sql` | `tenancy_sistemaventa` |
| `03-tenant-hotelprueba.sql` | `tenancy_hotelprueba` |
| `04-tenant-econocomercio.sql` | `tenancy_econocomercio` |

Cada dump incluye `CREATE DATABASE`, `CREATE TABLE`, índices, claves, triggers
y los `INSERT` con los datos actuales.

---

## 3. Restaurar un dump

```powershell
$mysql = "C:\xampp\Resources_xampp\mysql\bin\mysql.exe"

& $mysql -u root -p < ".\docs\sql\01-sistema-tenancy.sql"
& $mysql -u root -p < ".\docs\sql\02-tenant-sistemaventa.sql"
```

Verificar:

```sql
SHOW DATABASES LIKE 'tenancy%';
SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'tenancy';
SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'tenancy_sistemaventa';
```

Deben salir 20 y 259 respectivamente.

> Si importas sobre una base existente, las tablas se **eliminan y recrean**
> (`DROP TABLE IF EXISTS`). Haz backup antes.

---

## 4. Usuarios MySQL de los tenants

Los dumps no incluyen usuarios MySQL. La aplicación necesita un usuario por
tenant (`tenancy_sistemaventa`, `tenancy_hotelprueba`, ...) con permiso sobre su
base. La contraseña se deriva de `APP_KEY`:

```text
md5( APP_KEY + "." + id_del_website )
```

Créalos o repáralos con:

```powershell
php scripts\crear-usuarios-tenant.php
```

> Si restauras en otra PC con una `APP_KEY` distinta, **debes** ejecutar este
> script. Copia el `.env` original solo si controlas el destino: nunca lo
> publiques.

---

## 5. Restauración completa en otra PC

1. Instala PHP 7.4, Composer y MariaDB según el
   [README principal](../../README.md).
2. Clona el proyecto y crea el `.env` con `php artisan key:generate`.
3. Importa los dumps (sección 3) **o** crea el esquema desde cero con
   `php artisan migrate --seed` y luego crea los tenants desde el panel.
4. Ejecuta `php scripts\crear-usuarios-tenant.php`.
5. Verifica `php artisan migrate:status`.
6. Configura `hosts`, `public/storage` y `php artisan config:cache`.
7. Arranca con `php artisan serve` o `.\iniciar-systempe.bat`.

---
## 6. Inventario de tablas

### 6.1 Base del sistema `tenancy` (20 tablas)

<details>
<summary><strong>Base tenancy</strong> (20 tablas)</summary>

- `card_brands`
- `clients`
- `client_payments`
- `configurations`
- `failed_jobs`
- `history_resources`
- `hostnames`
- `jobs`
- `migrations`
- `modules`
- `module_levels`
- `module_level_client`
- `password_resets`
- `payment_method_types`
- `plans`
- `plan_documents`
- `restaurant_partners`
- `track_api_peru_services`
- `users`
- `websites`

</details>


### 6.2 Base de un tenant (259 tablas)

Las tres bases de tenant tienen **exactamente las mismas 259 tablas**; solo
cambian los datos.

<details>
<summary><strong>Base de tenant</strong> (259 tablas)</summary>

- `accounting_ledger`
- `accounting_ledger_task`
- `accounts`
- `account_payments`
- `banks`
- `bank_accounts`
- `bank_loans`
- `bank_loan_fee`
- `bank_loan_items`
- `bank_loan_payments`
- `bank_loan_reasons`
- `bank_loan_types`
- `billing_cycles`
- `brands`
- `business_turns`
- `card_brands`
- `carrier_guides`
- `carrier_guide_items`
- `cash`
- `cash_documents`
- `cash_document_credits`
- `cash_transactions`
- `categories`
- `cat_accounting_ledger_code_account`
- `cat_address_types`
- `cat_affectation_igv_types`
- `cat_attribute_types`
- `cat_charge_discount_types`
- `cat_colors_items`
- `cat_currency_types`
- `cat_detraction_types`
- `cat_digemid`
- `cat_document_types`
- `cat_identity_document_types`
- `cat_item_mold_cavities`
- `cat_item_mold_properties`
- `cat_item_package_measurements`
- `cat_item_product_family`
- `cat_item_size`
- `cat_item_status`
- `cat_item_units_per_package`
- `cat_item_unit_business`
- `cat_legend_types`
- `cat_note_credit_types`
- `cat_note_debit_types`
- `cat_operation_types`
- `cat_other_tax_concept_types`
- `cat_payment_method_types`
- `cat_perception_types`
- `cat_periods`
- `cat_price_types`
- `cat_related_documents_types`
- `cat_related_tax_document_types`
- `cat_retention_types`
- `cat_summary_status_types`
- `cat_system_isc_types`
- `cat_transfer_reason_types`
- `cat_transport_mode_types`
- `cat_unit_types`
- `charge_padrones`
- `client_errors`
- `client_error_types`
- `columns_to_reports`
- `companies`
- `company_accounts`
- `configurations`
- `configuration_ecommerce`
- `configuration_mi_tienda_pe`
- `contracts`
- `contract_items`
- `contract_payments`
- `contract_state_types`
- `countries`
- `departments`
- `devolutions`
- `devolution_items`
- `devolution_reasons`
- `dispatchers`
- `dispatches`
- `dispatch_items`
- `districts`
- `documentary_actions`
- `documentary_documents`
- `documentary_files`
- `documentary_files_archives`
- `documentary_files_requirements`
- `documentary_file_offices`
- `documentary_guides_number`
- `documentary_guides_number_status`
- `documentary_observation`
- `documentary_offices`
- `documentary_processes`
- `documentary_processes_rel_file`
- `documentary_processes_rel_off`
- `documentary_processes_rel_req`
- `documents`
- `document_fee`
- `document_hotels`
- `document_items`
- `document_payments`
- `document_transports`
- `download_tray`
- `drivers`
- `email_send_log`
- `establishments`
- `exchange_rates`
- `expenses`
- `expense_items`
- `expense_method_types`
- `expense_payments`
- `expense_reasons`
- `expense_types`
- `fixed_asset_items`
- `fixed_asset_purchases`
- `fixed_asset_purchase_items`
- `format_templates`
- `full_suscription_server_data`
- `full_suscription_user_data`
- `general_payment_conditions`
- `global_payments`
- `groups`
- `guide_files`
- `hotel_categories`
- `hotel_floors`
- `hotel_rates`
- `hotel_rents`
- `hotel_rent_items`
- `hotel_rooms`
- `hotel_room_rates`
- `income`
- `income_items`
- `income_payments`
- `income_reasons`
- `income_types`
- `inventories`
- `inventories_transfer`
- `inventory_configurations`
- `inventory_kardex`
- `inventory_transactions`
- `invoices`
- `items`
- `items_rating`
- `item_color`
- `item_images`
- `item_lots`
- `item_lots_group`
- `item_mold_cavities`
- `item_mold_properties`
- `item_movement`
- `item_movement_rel_extra`
- `item_package_measurements`
- `item_product_family`
- `item_rel_suscription_plans`
- `item_sets`
- `item_size`
- `item_status`
- `item_supplies`
- `item_tags`
- `item_types`
- `item_units_per_package`
- `item_unit_business`
- `item_unit_types`
- `item_warehouse`
- `item_warehouse_prices`
- `kardex`
- `machines`
- `machine_types`
- `migrations`
- `migration_configuration`
- `mill`
- `mill_items`
- `mi_tienda_pe`
- `modules`
- `module_levels`
- `module_level_user`
- `module_user`
- `notes`
- `offline_configurations`
- `orders`
- `order_forms`
- `order_form_items`
- `order_notes`
- `order_note_items`
- `packaging`
- `padrones`
- `password_resets`
- `payment_conditions`
- `payment_configurations`
- `payment_files`
- `payment_links`
- `payment_link_types`
- `payment_method_types`
- `perceptions`
- `perception_documents`
- `persons`
- `person_address`
- `person_addresses`
- `person_types`
- `preprinted_format_templates`
- `production`
- `promotions`
- `provinces`
- `purchases`
- `purchase_fee`
- `purchase_items`
- `purchase_orders`
- `purchase_order_items`
- `purchase_payments`
- `purchase_quotations`
- `purchase_quotation_items`
- `purchase_settlements`
- `purchase_settlement_items`
- `quotations`
- `quotation_items`
- `quotation_payments`
- `rel_user_to_documentary_offices`
- `report_configurations`
- `restaurant_configurations`
- `restaurant_roles`
- `retentions`
- `retention_documents`
- `sale_notes`
- `sale_note_items`
- `sale_note_migration`
- `sale_note_payments`
- `sale_opportunities`
- `sale_opportunity_files`
- `sale_opportunity_items`
- `series`
- `series_configurations`
- `skins`
- `soap_types`
- `state_types`
- `status_orders`
- `summaries`
- `summary_documents`
- `suscription_grade`
- `suscription_plans`
- `suscription_section`
- `tags`
- `tasks`
- `technical_services`
- `technical_service_items`
- `technical_service_payments`
- `tips`
- `track_api_peru_services`
- `transactions`
- `transaction_queries`
- `transaction_states`
- `transfer_account_payments`
- `users`
- `user_commissions`
- `user_rel_suscription_plans`
- `voided`
- `voided_documents`
- `warehouses`
- `web_platforms`
- `workers`
- `zones`

</details>

