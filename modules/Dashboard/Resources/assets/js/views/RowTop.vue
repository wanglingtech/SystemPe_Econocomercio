<template>
  <div class="dashboard-stats-row">
    <!-- Certificado -->
    <div class="stat-card cert-card" v-if="company.certificate_due">
      <div class="stat-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Cert. Digital</span>
        <span class="stat-value cert-value" :class="{'cert-danger': isDueWarning, 'cert-ok': !isDueWarning}">
          {{ company.certificate_due }}
        </span>
        <span class="cert-badge" :class="isDueWarning ? 'badge-danger' : 'badge-ok'">
          {{ isDueWarning ? '⚠ Por vencer' : '✓ Vigente' }}
        </span>
      </div>
    </div>

    <!-- CPE Emitidos -->
    <div class="stat-card cpe-card">
      <div class="stat-icon icon-blue">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Comprobantes emitidos</span>
        <span class="stat-value value-blue">{{ total_cpe }}</span>
        <span class="stat-sublabel">documentos electrónicos</span>
      </div>
      <div class="stat-bar bar-blue"></div>
    </div>

    <!-- Monto Comprobantes -->
    <div class="stat-card comp-card">
      <div class="stat-icon icon-purple">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Ventas en comprobantes</span>
        <span class="stat-value value-purple">S/ {{ formatNumber(document_total_global) }}</span>
        <span class="stat-sublabel">{{ formatFull(document_total_global) }}</span>
      </div>
      <div class="stat-bar bar-purple"></div>
    </div>

    <!-- Monto Notas de Venta -->
    <div class="stat-card notes-card">
      <div class="stat-icon icon-teal">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Ventas en notas</span>
        <span class="stat-value value-teal">S/ {{ formatNumber(sale_note_total_global) }}</span>
        <span class="stat-sublabel">{{ formatFull(sale_note_total_global) }}</span>
      </div>
      <div class="stat-bar bar-teal"></div>
    </div>

    <!-- Monto General -->
    <div class="stat-card total-card">
      <div class="stat-icon icon-orange">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Ingresos totales</span>
        <span class="stat-value value-orange">S/ {{ formatNumber(total) }}</span>
        <span class="stat-sublabel">{{ formatFull(total) }}</span>
      </div>
      <div class="stat-bar bar-orange"></div>
    </div>

    <!-- Utilidad Neta -->
    <div class="stat-card util-card" v-if="utilities.totals">
      <div class="stat-icon icon-green">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      </div>
      <div class="stat-content">
        <span class="stat-label">Utilidad neta</span>
        <span class="stat-value value-green">S/ {{ formatNumber(utilities.totals.utility) }}</span>
        <span class="stat-sublabel">{{ formatFull(utilities.totals.utility) }}</span>
      </div>
      <div class="stat-bar bar-green"></div>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  props: ["company", "utilities", "establishmentId"],
  data() {
    return {
      document_total_global: 0,
      total_cpe: 0,
      sale_note_total_global: 0,
      total: 0,
    };
  },
  mounted() {
    this.onFetchData();
  },
  watch: {
    establishmentId(val) {
      this.onFetchData();
    },
  },
  computed: {
    isDueWarning() {
      if (this.company.certificate_due) {
        const dueDate = moment(this.company.certificate_due);
        const now = moment();
        const diffInDays = dueDate.diff(now, "days");
        return diffInDays <= 15;
      }
      return false;
    },
  },
  methods: {
    formatNumber(value) {
      const num = parseFloat(value) || 0;
      if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
      if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
      return num.toFixed(2);
    },
    formatFull(value) {
      const num = parseFloat(value) || 0;
      return 'S/ ' + num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    onFetchData() {
      this.$http
        .get(`/dashboard/global-data?establishment_id=${this.establishmentId}`)
        .then((response) => {
          const data = response.data;
          this.document_total_global = data.document_total_global;
          this.total_cpe = data.total_cpe;
          this.sale_note_total_global = data.sale_note_total_global;
          this.total =
            parseFloat(this.document_total_global) +
            parseFloat(this.sale_note_total_global);
        });
    },
  },
};
</script>

<style scoped>
.dashboard-stats-row {
  display: flex;
  gap: 14px;
  padding: 16px 0 8px 0;
  flex-wrap: wrap;
}

.stat-card {
  flex: 1;
  min-width: 150px;
  background: #fff;
  border-radius: 16px;
  padding: 18px 16px 14px 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.10);
}

.stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-blue   { background: #e8f0fe; color: #4285f4; }
.icon-purple { background: #f3e8ff; color: #7c3aed; }
.icon-teal   { background: #e0f7f7; color: #0d9488; }
.icon-orange { background: #fff3e0; color: #f59e0b; }
.icon-green  { background: #e8f5e9; color: #22c55e; }

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.stat-label {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: normal;
  overflow: visible;
  text-overflow: clip;
  line-height: 1.25;
}

.stat-value {
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.value-blue   { color: #4285f4; }
.value-purple { color: #7c3aed; }
.value-teal   { color: #0d9488; }
.value-orange { color: #f59e0b; }
.value-green  { color: #22c55e; }

.stat-sublabel {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 0 0 16px 16px;
}

.bar-blue   { background: linear-gradient(90deg, #4285f4, #74b0ff); }
.bar-purple { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.bar-teal   { background: linear-gradient(90deg, #0d9488, #34d399); }
.bar-orange { background: linear-gradient(90deg, #f59e0b, #fcd34d); }
.bar-green  { background: linear-gradient(90deg, #22c55e, #86efac); }

.cert-card { border: 1px solid #e2e8f0; }
.cert-value { font-size: 15px !important; font-weight: 700; }
.cert-ok    { color: #22c55e; }
.cert-danger { color: #ef4444; }
.cert-badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  margin-top: 2px;
  width: fit-content;
}
.badge-ok     { background: #dcfce7; color: #16a34a; }
.badge-danger { background: #fee2e2; color: #dc2626; }
</style>