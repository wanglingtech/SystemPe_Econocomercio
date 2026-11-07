<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Guías de Transportista</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a href="/carrier-guides/create" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fa fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-body">
                <data-table-dispatch resource="carrier-guides">
                    <tr slot="heading">
                        <th>#</th>
                        <th class="text-center">Fecha Emisión</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Número</th>
                        <th>Transportista</th>
                        <th>Placa</th>
                        <th>Estado</th>
                        <th class="text-center">Fecha Traslado</th>
                        <th class="text-center">Descargas</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td class="text-center">{{ row.date_of_issue }}</td>
                        <td>{{ row.sender_name }}</td>
                        <td>{{ row.receiver_name }}</td>
                        <td>{{ row.number }}</td>
                        <td>{{ row.carrier_name }}</td>
                        <td>{{ row.license_plate }}</td>
                        <td>
                            <span class="badge text-white"
                                :class="{
                                    'bg-secondary': row.state_type_id === '01',
                                    'bg-info':      row.state_type_id === '03',
                                    'bg-success':   row.state_type_id === '05',
                                    'bg-dark':      row.state_type_id === '09'
                                }">
                                {{ row.state_type_description }}
                            </span>
                        </td>
                        <td class="text-center">{{ row.date_of_shipping }}</td>
                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                v-if="row.has_xml"
                                @click.prevent="clickDownload(row.download_external_xml)">XML</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                v-if="row.has_pdf"
                                @click.prevent="clickDownload(row.download_external_pdf)">PDF</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                v-if="row.has_cdr"
                                @click.prevent="clickDownload(row.download_external_cdr)">CDR</button>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                @click.prevent="clickOptions(row.id)">Opciones</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                v-if="['01','09'].includes(row.state_type_id)"
                                @click.prevent="clickDelete(row.id)">Anular</button>
                        </td>
                    </tr>
                </data-table-dispatch>
            </div>
        </div>

        <carrier-guide-options
            :showDialog.sync="showDialogOptions"
            :recordId="recordId"
            :showClose="true">
        </carrier-guide-options>

    </div>
</template>

<script>
import DataTableDispatch from '../../../components/DataTableDispatch.vue'
import CarrierGuideOptions from './partials/options.vue'

export default {
    components: { DataTableDispatch, CarrierGuideOptions },
    data() {
        return {
            resource: 'carrier-guides',
            showDialogOptions: false,
            recordId: null,
        }
    },
    methods: {
        clickOptions(id) {
            this.recordId = id
            this.showDialogOptions = true
        },
        clickDownload(url) {
            window.open(url, '_blank')
        },
        async clickDelete(id) {
            try {
                await this.$confirm('¿Desea anular esta guía de transportista?', 'Confirmar', {
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar',
                    type: 'warning'
                })
                const { data } = await this.$http.delete(`/carrier-guides/${id}`)
                if (data.success) {
                    this.$message.success(data.message)
                    this.$eventHub.$emit('reloadData')
                } else {
                    this.$message.error(data.message)
                }
            } catch (e) {}
        },
    }
}
</script>