<?php
// index.php — Generador de Test Plan (v2.1 basado en v2.0, con checkboxes en grid)

date_default_timezone_set('America/Argentina/Buenos_Aires');
$today = date('Y-m-d');

$REF_TYPES = ['Requisito','Historia','Épica','Mockup','Diseño','Doc','Otro'];

$TEST_TYPES = [
  'Funcionales','Regresion','Integración','UAT','Exploratorio','Estrés','Performance',
  'Seguridad','Compatibilidad','Usabilidad','Interrupción (Mobile)','APIs','Automatizadas',
  'A/B Testing','Pruebas de Humo','Accesibilidad','E2E','Pruebas de UI',
  'Pruebas de monitoreo','Resiliencia','Pruebas de Volumen'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Generador de Test Plan</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="assets/js/app.js" defer></script>
</head>
<body>
  <header class="header" role="banner">
    <div class="header-wrap">
      <h1 class="title">Generador de Test Plan</h1>
      <p class="subtitle">Ingresá lo mínimo. El sistema arma un documento limpio y presentable.</p>
    </div>
  </header>

  <main class="container" role="main">
    <form id="planForm" action="generate_plan.php" method="POST" novalidate>
      <!-- Portada -->
      <section class="card">
        <h2>Portada</h2>
        <div class="grid grid-3">
          <label class="field">
            <span>Nombre del proyecto</span>
            <input name="project_name" type="text" required placeholder="Ej: WalletPay" />
          </label>
          <label class="field">
            <span>Fecha</span>
            <input name="date" type="date" required value="<?= $today ?>" />
          </label>
          <label class="field">
            <span>Versión del plan</span>
            <input name="plan_version" type="text" required placeholder="Ej: 2.1" />
          </label>
        </div>
      </section>

      <!-- Introducción -->
      <section class="card">
        <h2>Introducción</h2>
        <label class="field">
          <span>Objetivo (breve)</span>
          <textarea name="objective" rows="3" placeholder="Definir estrategia y alcance de QA para la release actual."></textarea>
        </label>
        <div class="grid grid-2">
          <label class="field">
            <span>Alcance (uno por línea)</span>
            <textarea name="scope" rows="4" placeholder="Login y registro
Transferencias y QR
Panel de administración"></textarea>
          </label>
          <label class="field">
            <span>Exclusiones / fuera de alcance (uno por línea)</span>
            <textarea name="out_of_scope" rows="4" placeholder="Integración con banco externo
Pruebas de estrés extremo
Traducciones adicionales"></textarea>
          </label>
        </div>
      </section>

      <!-- Referencias -->
      <section class="card">
        <h2>Referencias</h2>

        <div class="refs-header">
          <span aria-hidden="true"></span>
          <span>Descripción</span>
          <span>URL</span>
          <span>Tipo</span>
          <span aria-hidden="true"></span>
        </div>

        <div id="refsList" class="refs-list" aria-live="polite">
          <div class="ref-row" data-index="0" draggable="true">
            <button type="button" class="btn-handle" title="Arrastrar para reordenar" aria-label="Mover">⋮⋮</button>
            <input type="text" name="references[0][title]" class="ref-title" placeholder="Ej. Historia US-123" required />
            <input type="text" name="references[0][url]"   class="ref-url"   placeholder="https://example.com" required />
            <select name="references[0][type]" class="ref-type">
              <?php foreach ($REF_TYPES as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn-del" title="Eliminar" aria-label="Eliminar">✕</button>
          </div>
        </div>

        <button id="addRefBtn" type="button" class="link-add">+ Agregar referencia</button>

        <template id="refTemplate">
          <div class="ref-row" data-index="$idx" draggable="true">
            <button type="button" class="btn-handle" title="Arrastrar para reordenar" aria-label="Mover">⋮⋮</button>
            <input  type="text" name="references[$idx][title]" class="ref-title" placeholder="Descripción" required />
            <input  type="text" name="references[$idx][url]"   class="ref-url"   placeholder="https://example.com" required />
            <select name="references[$idx][type]" class="ref-type">
              <?php foreach ($REF_TYPES as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn-del" title="Eliminar" aria-label="Eliminar">✕</button>
          </div>
        </template>
      </section>

      <!-- Estrategia de pruebas -->
      <section class="card">
        <h2>Estrategia de pruebas</h2>
        <div class="chips" role="group" aria-label="Tipos de prueba">
          <?php foreach ($TEST_TYPES as $t): ?>
            <label class="chip">
              <input type="checkbox" name="test_types[]" value="<?= htmlspecialchars($t) ?>">
              <span><?= htmlspecialchars($t) ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <label class="field" style="margin-top:12px">
          <span>Herramientas</span>
          <div id="toolsTags" class="tags-input" data-name="tools[]">
            <div class="tags-wrap" id="toolsWrap"></div>
            <input id="toolsInput" type="text" placeholder="Escribí y presioná Enter o coma" />
          </div>
          <small class="muted">Agregá herramientas (Playwright, Postman, JMeter, OWASP ZAP, etc.).</small>
        </label>
      </section>

      <!-- Criterios -->
      <section class="card">
        <h2>Criterios</h2>
        <div class="grid grid-2">
          <label class="field">
            <span>Entrada: ¿Qué debe estar listo antes de empezar? (uno por línea)</span>
            <textarea name="entry_criteria" rows="3" placeholder="Código deployado en QA
Accesos habilitados
Datos de prueba disponibles"></textarea>
          </label>
          <label class="field">
            <span>Salida: ¿Qué condiciones deben cumplirse para dar por finalizada la fase? (uno por línea)</span>
            <textarea name="exit_criteria" rows="3" placeholder="0 bloqueantes
≤ 5% casos fallidos
Reporte final aprobado"></textarea>
          </label>
        </div>
      </section>

      <!-- Entorno -->
      <section class="card">
        <h2>Entorno de pruebas</h2>
        <div class="grid grid-2">
          <label class="field">
            <span>Ambiente (URL)</span>
            <input name="env_url" type="url" placeholder="https://qa.tuapp.com" />
          </label>
          <label class="field">
            <span>Roles a usar</span>
            <input name="env_roles" type="text" placeholder="Admin, Soporte, Usuario final" />
          </label>
        </div>
      </section>

      <!-- Roles y responsabilidades -->
      <section class="card">
        <h2>Roles y responsabilidades</h2>
        <div class="team-header">
          <span aria-hidden="true"></span>
          <span>Nombre</span>
          <span>Rol</span>
          <span>Contacto (Email)</span>
          <span aria-hidden="true"></span>
        </div>

        <div id="teamList" class="team-list" aria-live="polite">
          <div class="team-row" data-index="0" draggable="true">
            <button type="button" class="btn-handle" title="Arrastrar para reordenar" aria-label="Mover">⋮⋮</button>
            <input type="text"  name="team[0][name]"  class="team-name"  placeholder="Nombre y Apellido" />
            <input type="text"  name="team[0][role]"  class="team-role"  placeholder="Rol (p. ej. QA Engineer)" />
            <input type="email" name="team[0][email]" class="team-email" placeholder="correo@empresa.com" />
            <button type="button" class="btn-del" title="Eliminar" aria-label="Eliminar">✕</button>
          </div>
        </div>

        <button id="addTeamBtn" type="button" class="link-add">+ Agregar integrante</button>

        <template id="teamTemplate">
          <div class="team-row" data-index="$idx" draggable="true">
            <button type="button" class="btn-handle" title="Arrastrar para reordenar" aria-label="Mover">⋮⋮</button>
            <input type="text"   name="team[$idx][name]"  class="team-name"  placeholder="Nombre y Apellido" />
            <input type="text"   name="team[$idx][role]"  class="team-role"  placeholder="Rol (p. ej. QA Engineer)" />
            <input type="email"  name="team[$idx][email]" class="team-email" placeholder="correo@empresa.com" />
            <button type="button" class="btn-del" title="Eliminar" aria-label="Eliminar">✕</button>
          </div>
        </template>
      </section>

      <!-- Cronograma y recursos -->
      <section class="card">
        <h2>Cronograma y recursos</h2>
        <div class="grid grid-3">
          <label class="field">
            <span>Inicio</span>
            <input name="start_date" type="date" />
          </label>
          <label class="field">
            <span>Fin</span>
            <input name="end_date" type="date" />
          </label>
          <label class="field">
            <span>Headcount</span>
            <input name="headcount" type="number" min="1" value="2" />
          </label>
        </div>
      </section>

      <!-- Gestión de riesgos -->
      <section class="card">
        <h2>Gestión de riesgos</h2>
        <p class="hint">Ingresá 2–4 riesgos (uno por línea) con probabilidad e impacto 1–5. Formato: <b>Texto;prob;impacto</b></p>
        <textarea name="risks" rows="4" placeholder="Demora en accesos;4;3
Ambiente inestable;3;4"></textarea>
      </section>

      <!-- Casos de prueba & KPIs -->
      <section class="card">
        <h2>Casos de prueba & KPIs</h2>
        <p class="hint">Casos: uno por línea con formato <b>ID;Descripción</b>.</p>
        <textarea name="testcases" rows="5" placeholder="TC-001;Login con credenciales válidas
TC-002;Recuperación de contraseña
TC-003;Transferencia con saldo suficiente"></textarea>

        <div class="grid grid-3 kpi-grid">
          <label class="field">
            <span>Total de casos</span>
            <input name="kpi_total" type="number" min="0" value="100" />
          </label>
          <label class="field">
            <span>Ejecutados</span>
            <input name="kpi_executed" type="number" min="0" value="0" />
          </label>
          <label class="field">
            <span>Pasados</span>
            <input name="kpi_passed" type="number" min="0" value="0" />
          </label>
          <label class="field">
            <span>Defectos abiertos</span>
            <input name="kpi_open" type="number" min="0" value="0" />
          </label>
          <label class="field">
            <span>Defectos cerrados</span>
            <input name="kpi_closed" type="number" min="0" value="0" />
          </label>
        </div>
      </section>

      <div class="actions">
        <button type="submit" class="btn-primary">Generar Test Plan</button>
      </div>
    </form>
  </main>

  <footer class="footer" role="contentinfo">
    <small>v2.1 • Estructura completa + UI compacta, Segoe UI, link-add dark, checkboxes en columnas</small>
  </footer>
</body>
</html>
