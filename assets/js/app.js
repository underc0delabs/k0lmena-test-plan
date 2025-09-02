// app.js — v2.1 (DnD estable + tags + dinámicos)
document.addEventListener('DOMContentLoaded', () => {

  // ---------- Helpers
  const $ = sel => document.querySelector(sel);
  const $$ = sel => Array.from(document.querySelectorAll(sel));

  function createNodeFromHTML(html){
    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    return wrap.firstElementChild;
  }

  // ---------- Prevent form submit on Enter inside tags input
  const planForm = $('#planForm');
  planForm?.addEventListener('keydown', (e) => {
    if (e.target && e.target.id === 'toolsInput' && (e.key === 'Enter')) {
      e.preventDefault();
    }
  });

  // ---------- Tags input (tools)
  (function initTags(){
    const wrap = $('#toolsTags');
    if (!wrap) return;
    const input = $('#toolsInput');
    const list = $('#toolsWrap');
    const name = wrap.dataset.name || 'tools[]';

    function addTag(text){
      const val = text.trim();
      if (!val) return;
      const tag = document.createElement('span');
      tag.className = 'tag';
      tag.innerHTML = `<span>${val}</span><button type="button" class="tag-x" aria-label="Eliminar">✕</button>`;
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = name;
      hidden.value = val;
      tag.appendChild(hidden);
      list.appendChild(tag);
      input.value = '';
    }

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag(input.value);
      }
    });

    list.addEventListener('click', (e) => {
      if (e.target.closest('.tag-x')) {
        const tag = e.target.closest('.tag');
        tag?.remove();
      }
    });
  })();

  // ---------- Dynamic rows util
  function initDynamic(sectionId, templateId){
    const list = document.getElementById(sectionId);
    const tpl  = document.getElementById(templateId);
    if (!list || !tpl) return;
    let counter = 1;

    // Add row buttons
    const addBtn = list.parentElement.querySelector('.link-add');
    addBtn?.addEventListener('click', () => {
      const html = tpl.innerHTML.replaceAll('$idx', String(counter++));
      const node = createNodeFromHTML(html);
      list.appendChild(node);
    });

    // Delete with minimum 1 row
    list.addEventListener('click', (e) => {
      if (e.target.classList.contains('btn-del') || e.target.closest('.btn-del')) {
        const row = e.target.closest('.ref-row, .team-row');
        const rows = list.querySelectorAll('.ref-row, .team-row');
        if (rows.length <= 1) return; // never 0
        row.remove();
      }
    });

    // Drag & drop
    let dragged = null;
    list.addEventListener('dragstart', (e) => {
      const row = e.target.closest('.ref-row, .team-row');
      if (!row) return;
      dragged = row;
      row.classList.add('row-dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    list.addEventListener('dragend', (e) => {
      if (dragged) dragged.classList.remove('row-dragging');
      dragged = null;
      $$('.row-drag-over').forEach(el => el.classList.remove('row-drag-over'));
    });
    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      const row = e.target.closest('.ref-row, .team-row');
      if (!row || row === dragged) return;
      row.classList.add('row-drag-over');
      const rect = row.getBoundingClientRect();
      const before = (e.clientY - rect.top) < rect.height / 2;
      row.parentNode.insertBefore(dragged, before ? row : row.nextSibling);
    });
    list.addEventListener('dragleave', (e) => {
      const row = e.target.closest('.ref-row, .team-row');
      row?.classList.remove('row-drag-over');
    });
  }

  // Initialize for references and team
  initDynamic('refsList', 'refTemplate');
  initDynamic('teamList', 'teamTemplate');
});
