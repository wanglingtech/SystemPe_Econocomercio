<template>
    <el-dialog :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               width="30%"
               @open="create">

        <div class="row">
            <div v-if="form && form.external_id"
                 class="col-lg-12 col-md-12 col-sm-12 text-center font-weight-bold mt-3">
                <button class="btn btn-lg btn-info waves-effect waves-light"
                        type="button"
                        @click="clickDownload()">
                    <i class="fa fa-file-alt"></i>
                </button>
                <p>Descargar A4</p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <el-input v-model="form.receiver_email" placeholder="">
                    <el-button slot="append"
                               :loading="loading"
                               icon="el-icon-message"
                               @click="clickSendEmail">Enviar
                    </el-button>
                </el-input>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <el-input v-model="form.receiver_telephone">
                    <template slot="prepend">+51</template>
                    <el-button slot="append"
                               @click="clickSendWhatsapp">Enviar
                        <el-tooltip class="item"
                                    content="Es necesario tener aperturado Whatsapp web"
                                    effect="dark"
                                    placement="top-start">
                            <i class="fab fa-whatsapp"></i>
                        </el-tooltip>
                    </el-button>
                </el-input>
            </div>
        </div>

        <span slot="footer" class="dialog-footer">
            <el-button @click="clickClose">Cerrar</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ['showDialog', 'recordId', 'showClose'],
    data() {
        return {
            titleDialog: null,
            loading: false,
            resource: 'carrier-guides',
            form: {},
        }
    },
    created() {
        this.initForm()
    },
    methods: {
        initForm() {
            this.form = {
                external_id: null,
                number: null,
                id: null,
                receiver_email: null,
                receiver_telephone: null,
                download_external_pdf: null,
            }
        },
        async create() {
            await this.$http.get(`/${this.resource}/${this.recordId}`).then(response => {
                const data = response.data.data
                this.form = {
                    external_id:          data.external_id,
                    number:               data.series + '-' + String(data.number).padStart(8, '0'),
                    id:                   data.id,
                    receiver_email:       data.receiver ? (typeof data.receiver === 'string' ? JSON.parse(data.receiver).email : data.receiver.email) : null,
                    receiver_telephone:   data.receiver ? (typeof data.receiver === 'string' ? JSON.parse(data.receiver).telephone : data.receiver.telephone) : null,
                    download_external_pdf: data.download_external_pdf,
                }
                this.titleDialog = 'Guía: ' + this.form.number
            })
        },
        clickDownload() {
            window.open(this.form.download_external_pdf, '_blank')
        },
        clickSendWhatsapp() {
            if (!this.form.receiver_telephone) {
                return this.$message.error('El número es obligatorio')
            }
            const text = `Estimado cliente, adjuntamos su Guía de Transportista N° ${this.form.number}`
            window.open(`https://wa.me/51${this.form.receiver_telephone}?text=${encodeURIComponent(text)}`, '_blank')
        },
        clickSendEmail() {
            if (!this.form.receiver_email) {
                return this.$message.error('El correo es obligatorio')
            }
            this.loading = true
            this.$http.post(`/${this.resource}/email`, {
                receiver_email: this.form.receiver_email,
                id: this.form.id
            }).then(response => {
                if (response.data.success) {
                    this.$message.success('El correo fue enviado satisfactoriamente')
                } else {
                    this.$message.error('Error al enviar el correo')
                }
            }).catch(() => {
                this.$message.error('Error al enviar el correo')
            }).then(() => {
                this.loading = false
            })
        },
        clickClose() {
            this.$emit('update:showDialog', false)
            this.initForm()
        },
    }
}
</script>