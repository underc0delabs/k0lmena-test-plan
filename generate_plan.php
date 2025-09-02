<?php
// generate_plan.php — Render del Test Plan (v1.9.6)

function normalize_lines(string $str): array {
  return array_values(array_filter(array_map('trim', preg_split('/\R/', $str))));
}
function bullets(?string $str): string {
  $items = normalize_lines($str ?? '');
  if (!$items) return '<em>No especificado</em>';
  $lis = '';
  foreach ($items as $line) $lis .= '<li>'.htmlspecialchars($line).'</li>';
  return "<ul>{$lis}</ul>";
}
function table_simple(array $rows, array $headers): string {
  if (!$rows) return '<p><em>Sin datos</em></p>';
  $ths = ''; foreach ($headers as $h) $ths .= '<th>'.htmlspecialchars($h).'</th>';
  $trs = '';
  foreach ($rows as $r) {
    $trs .= '<tr>';
    foreach ($r as $cell) $trs .= '<td>'.htmlspecialchars((string)$cell).'</td>';
    $trs .= '</tr>';
  }
  return "<table class='flat'><thead><tr>{$ths}</tr></thead><tbody>{$trs}</tbody></table>";
}
function render_references_list(array $refs): string {
  if (!$refs) return '<p><em>Sin referencias</em></p>';
  $items = '';
  foreach ($refs as $ref) {
    $type  = trim((string)($ref['type']  ?? ''));
    $title = trim((string)($ref['title'] ?? ''));
    $url   = trim((string)($ref['url']   ?? ''));
    $badge = $type ? '<span class="badge">'.htmlspecialchars($type).'</span> ' : '';
    $link  = filter_var($url, FILTER_VALIDATE_URL)
      ? '<a href="'.htmlspecialchars($url).'" target="_blank" rel="noopener">'.htmlspecialchars($url).'</a>'
      : htmlspecialchars($url);
    $items .= '<li class="ref-output-item">'.$badge.'<strong>'.htmlspecialchars($title).'</strong> — '.$link.'</li>';
  }
  return '<ul class="refs-output">'.$items.'</ul>';
}

$d = $_POST;

$project   = htmlspecialchars($d['project_name'] ?? 'Proyecto');
$date      = htmlspecialchars($d['date'] ?? '');
$version   = htmlspecialchars($d['plan_version'] ?? '1.9.6');

$objective = $d['objective']    ?? '';
$scope_raw = $d['scope']        ?? '';
$out_raw   = $d['out_of_scope'] ?? '';

// Referencias
$references_arr = [];
if (isset($d['references']) && is_array($d['references'])) {
  foreach ($d['references'] as $it) {
    if (!is_array($it)) continue;
    $references_arr[] = [
      'title' => $it['title'] ?? '',
      'url'   => $it['url']   ?? '',
      'type'  => $it['type']  ?? '',
    ];
  }
}

// Estrategia
$test_types = isset($d['test_types']) && is_array($d['test_types'])
  ? array_map('htmlspecialchars', $d['test_types'])
  : [];
$test_types_str = $test_types ? implode(', ', $test_types) : '<em>Sin seleccionar</em>';

$tools_val = $d['tools'] ?? [];
if (is_array($tools_val)) {
  $tools_arr = array_values(array_filter(array_map('trim', $tools_val)));
  $tools_str = $tools_arr ? implode(', ', array_map('htmlspecialchars', $tools_arr)) : '';
} else {
  $tools_str = htmlspecialchars(trim((string)$tools_val));
}

// Criterios
$entry     = $d['entry_criteria'] ?? '';
$exit      = $d['exit_criteria']  ?? '';

// Entorno
$env_url   = htmlspecialchars($d['env_url']   ?? '');
$env_roles = htmlspecialchars($d['env_roles'] ?? '');

// Equipo
$team_rows = [];
if (isset($d['team']) && is_array($d['team'])) {
  foreach ($d['team'] as $member) {
    if (!is_array($member)) continue;
    $name  = trim((string)($member['name']  ?? ''));
    $role  = trim((string)($member['role']  ?? ''));
    $email = trim((string)($member['email'] ?? ''));
    if ($name || $role || $email) $team_rows[] = [$name, $role, $email];
  }
}

// Cronograma & KPIs & Riesgos & Casos
$start     = htmlspecialchars($d['start_date'] ?? '');
$end       = htmlspecialchars($d['end_date']   ?? '');
$headcount = (int)($d['headcount'] ?? 1);

$risks_raw   = $d['risks']     ?? '';
$cases_raw   = $d['testcases'] ?? '';
$kpi_total   = (int)($d['kpi_total']    ?? 0);
$kpi_exec    = (int)($d['kpi_executed'] ?? 0);
$kpi_pass    = (int)($d['kpi_passed']   ?? 0);
$kpi_open    = (int)($d['kpi_open']     ?? 0);
$kpi_closed  = (int)($d['kpi_closed']   ?? 0);

$scope_items = normalize_lines($scope_raw);
$out_items   = normalize_lines($out_raw);
$kpi_fail   = max(0, $kpi_exec - $kpi_pass);
$kpi_notrun = max(0, $kpi_total - $kpi_exec);

// Riesgos
$risk_rows = $risk_scores = [];
foreach (normalize_lines($risks_raw) as $line) {
  [$txt, $p, $i] = array_pad(array_map('trim', explode(';', $line)), 3, '');
  $p = max(1, min(5, (int)$p));
  $i = max(1, min(5, (int)$i));
  $risk_rows[]   = [$txt, $p, $i];
  $risk_scores[] = $p * $i;
}

// Casos
$case_rows = [];
foreach (normalize_lines($cases_raw) as $line) {
  [$id, $desc] = array_pad(array_map('trim', explode(';', $line)), 2, '');
  $case_rows[] = [$id, $desc];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $project ?> — Test Plan v<?= $version ?></title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="doc">
  <div class="doc-header">
    <div class="doc-meta">
      <h1><?= $project ?> — Test Plan</h1>
      <p><strong>Fecha:</strong> <?= $date ?> · <strong>Versión:</strong> <?= $version ?></p>
    </div>
    <div class="doc-actions">
      <button type="button" class="btn" onclick="window.print()">Imprimir / PDF</button>
      <a class="btn btn-secondary" href="index.php">Cargar nuevo</a>
    </div>
  </div>

  <section class="section">
    <h2>Introducción</h2>
    <h3>Objetivo</h3>
    <p><?= $objective ? nl2br(htmlspecialchars($objective)) : 'Definir la estrategia y el alcance de QA para la release actual.' ?></p>
    <div class="grid grid-2">
      <div><h3>Alcance</h3><?= bullets($scope_raw) ?></div>
      <div><h3>Exclusiones / Fuera de alcance</h3><?= bullets($out_raw) ?></div>
    </div>
    <div class="charts-50">
      <div class="chart-card">
        <h3 class="chart-title">Alcance vs. Exclusiones</h3>
        <div class="chart-box"><canvas id="scopeChart" class="chart-canvas"></canvas></div>
      </div>
    </div>
  </section>

  <section class="section">
    <h2>Referencias</h2>
    <?= render_references_list($references_arr) ?>
  </section>

  <section class="section">
    <h2>Estrategia de pruebas</h2>
    <p><strong>Tipos:</strong> <?= $test_types_str ?></p>
    <p><strong>Herramientas:</strong> <?= $tools_str ?: '<em>Por definir</em>' ?></p>
  </section>

  <section class="section">
    <h2>Criterios</h2>
    <div class="grid grid-2">
      <div><h3>Entrada</h3><?= bullets($entry) ?></div>
      <div><h3>Salida</h3><?= bullets($exit) ?></div>
    </div>
  </section>

  <section class="section">
    <h2>Entorno de pruebas</h2>
    <div class="grid grid-2">
      <p><strong>URL:</strong> <?= $env_url ?: '<em>Por definir</em>' ?></p>
      <p><strong>Roles:</strong> <?= $env_roles ?: '<em>Por definir</em>' ?></p>
    </div>
  </section>

  <section class="section">
    <h2>Roles y responsabilidades</h2>
    <?= table_simple($team_rows, ['Nombre','Rol','Contacto']) ?>
  </section>

  <section class="section">
    <h2>Cronograma y recursos</h2>
    <div class="grid grid-2">
      <div class="stack">
        <p><strong>Inicio:</strong> <?= $start ?: '—' ?> · <strong>Fin:</strong> <?= $end ?: '—' ?></p>
        <p><strong>Headcount:</strong> <?= $headcount ?></p>
      </div>
      <div>
        <div class="gantt">
          <div class="gantt-bar" style="width: 100%;">Planificación</div>
          <div class="gantt-bar" style="width: 60%;">Ejecución</div>
          <div class="gantt-bar" style="width: 30%;">Cierre</div>
        </div>
        <small class="muted">* Representación simple (MVP).</small>
      </div>
    </div>
  </section>

  <section class="section">
    <h2>Gestión de riesgos</h2>
    <?= table_simple($risk_rows, ['Riesgo','Prob.','Impacto']); ?>
    <div class="charts-50">
      <div class="chart-card">
        <h3 class="chart-title">Score de riesgos</h3>
        <div class="chart-box"><canvas id="riskChart" class="chart-canvas"></canvas></div>
      </div>
    </div>
  </section>

  <section class="section">
    <h2>Casos de prueba</h2>
    <?= table_simple($case_rows, ['ID','Descripción']); ?>
  </section>

  <section class="section">
    <h2>Métricas y reportes</h2>
    <?php
      $kpi_notrun = max(0, $kpi_total - $kpi_exec);
      $kpi_fail   = max(0, $kpi_exec - $kpi_pass);
    ?>
    <ul class="kpi-list">
      <li><strong>Total casos:</strong> <?= $kpi_total ?></li>
      <li><strong>Ejecutados:</strong> <?= $kpi_exec ?> (No ejecutados: <?= $kpi_notrun ?>)</li>
      <li><strong>Pasados:</strong> <?= $kpi_pass ?> (Fallidos: <?= $kpi_fail ?>)</li>
      <li><strong>Defectos abiertos/cerrados:</strong> <?= $kpi_open ?>/<?= $kpi_closed ?></li>
    </ul>

    <div class="charts-50">
      <div class="chart-card"><h3 class="chart-title">Ejecución de casos</h3><div class="chart-box"><canvas id="execChart" class="chart-canvas"></canvas></div></div>
      <div class="chart-card"><h3 class="chart-title">Defectos</h3><div class="chart-box"><canvas id="defectChart" class="chart-canvas"></canvas></div></div>
    </div>
  </section>

  <section class="section">
    <h2>Control de cambios</h2>
    <?= table_simple([[ $version, $date, 'UI compacta + DnD estable + Segoe UI + link-add dark.']], ['Versión','Fecha','Descripción']); ?>
  </section>

  <script>
    const P = { blue:'#4A90E2', gray:'#95A5A6', green:'#2ECC71', red:'#E74C3C', orange:'#E67E22' };
    const scopeData   = [<?= count($scope_items) ?>, <?= count($out_items) ?>];
    const riskScores  = <?= json_encode($risk_scores) ?>;
    const execData    = [<?= $kpi_pass ?>, <?= max(0, $kpi_exec - $kpi_pass) ?>, <?= max(0, $kpi_total - $kpi_exec) ?>];
    const defectsData = [<?= $kpi_open ?>, <?= $kpi_closed ?>];
    const commonOpt   = { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:14 } } }, layout:{ padding:8 } };

    new Chart(document.getElementById('scopeChart'), { type:'doughnut', data:{ labels:['En alcance','Fuera de alcance'], datasets:[{ data:scopeData, backgroundColor:[P.blue,P.gray], borderWidth:0 }] }, options:{ ...commonOpt, cutout:'65%' } });
    new Chart(document.getElementById('riskChart'),  { type:'bar', data:{ labels:riskScores.map((_,i)=>'R'+(i+1)), datasets:[{ data:riskScores, backgroundColor:P.blue, borderRadius:6 }] }, options:{ ...commonOpt, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,0.06)' } } } } });
    new Chart(document.getElementById('execChart'),  { type:'bar', data:{ labels:['Pasados','Fallidos','No ejecutados'], datasets:[{ data:execData, backgroundColor:[P.green,P.red,P.gray], borderRadius:6 }] }, options:{ ...commonOpt, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(255,255,255,0.06)' } } } } });
    new Chart(document.getElementById('defectChart'),{ type:'doughnut', data:{ labels:['Abiertos','Cerrados'], datasets:[{ data:defectsData, backgroundColor:[P.orange,P.green], borderWidth:0 }] }, options:{ ...commonOpt, cutout:'65%' } });
  </script>
</body>
</html>
