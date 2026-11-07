<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">Nueva Guía de Transportista</h3>
        </div>
        <div class="card-body">
            <form autocomplete="off" @submit.prevent="submit">
                <div class="form-body">
                    <div class="row">
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.series}" class="form-group">
                                <label class="control-label">Serie<span class="text-danger"> *</span></label>
                                <el-select v-model="form.series" style="width:100%">
                                    <el-option v-for="option in series" :key="option.id"
                                        :label="option.number" :value="option.number"></el-option>
                                </el-select>
                                <small v-if="errors.series" class="form-control-feedback" v-text="errors.series[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.date_of_issue}" class="form-group">
                                <label class="control-label">Fecha de emisión<span class="text-danger"> *</span></label>
                                <el-date-picker v-model="form.date_of_issue" :clearable="false"
                                    type="date" value-format="yyyy-MM-dd" style="width:100%"></el-date-picker>
                                <small v-if="errors.date_of_issue" class="form-control-feedback" v-text="errors.date_of_issue[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.date_of_shipping}" class="form-group">
                                <label class="control-label">Fecha de traslado<span class="text-danger"> *</span></label>
                                <el-date-picker v-model="form.date_of_shipping" :clearable="false"
                                    type="date" value-format="yyyy-MM-dd" style="width:100%"></el-date-picker>
                                <small v-if="errors.date_of_shipping" class="form-control-feedback" v-text="errors.date_of_shipping[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.transport_mode_type_id}" class="form-group">
                                <label class="control-label">Modo de traslado<span class="text-danger"> *</span></label>
                                <el-select v-model="form.transport_mode_type_id" style="width:100%">
                                    <el-option v-for="option in transportModeTypes" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.transport_mode_type_id" class="form-control-feedback" v-text="errors.transport_mode_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors.transfer_reason_type_id}" class="form-group">
                                <label class="control-label">Motivo de traslado<span class="text-danger"> *</span></label>
                                <el-select v-model="form.transfer_reason_type_id" style="width:100%">
                                    <el-option v-for="option in transferReasonTypes" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.transfer_reason_type_id" class="form-control-feedback" v-text="errors.transfer_reason_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors.transfer_reason_description}" class="form-group">
                                <label class="control-label">Descripción de motivo de traslado</label>
                                <el-input v-model="form.transfer_reason_description" :rows="2"
                                    maxlength="100" placeholder="Descripción..." type="textarea"></el-input>
                                <small v-if="errors.transfer_reason_description" class="form-control-feedback" v-text="errors.transfer_reason_description[0]"></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.unit_type_id}" class="form-group">
                                <label class="control-label">Unidad de medida<span class="text-danger"> *</span></label>
                                <el-select v-model="form.unit_type_id" style="width:100%">
                                    <el-option v-for="option in unitTypes" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.unit_type_id" class="form-control-feedback" v-text="errors.unit_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.total_weight}" class="form-group">
                                <label class="control-label">Peso total<span class="text-danger"> *</span></label>
                                <el-input-number v-model="form.total_weight" :max="9999999999"
                                    :min="0" :precision="2" :step="1"></el-input-number>
                                <small v-if="errors.total_weight" class="form-control-feedback" v-text="errors.total_weight[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div :class="{'has-danger': errors.packages_number}" class="form-group">
                                <label class="control-label">Número de paquetes</label>
                                <el-input-number v-model="form.packages_number" :max="9999999999"
                                    :min="0" :precision="0" :step="1"></el-input-number>
                                <small v-if="errors.packages_number" class="form-control-feedback" v-text="errors.packages_number[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors.observations}" class="form-group">
                                <label class="control-label">Observaciones</label>
                                <el-input v-model="form.observations" :rows="3" maxlength="250"
                                    placeholder="Observaciones..." type="textarea"></el-input>
                                <small v-if="errors.observations" class="form-control-feedback" v-text="errors.observations[0]"></small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4>Remitente y Destinatario</h4>
                    <div class="row">
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors.sender_id}" class="form-group">
                                <label class="control-label">
                                    Remitente<span class="text-danger"> *</span>
                                    <a href="#" class="ml-2 text-info font-weight-bold"
                                        @click.prevent="showDialogNewPerson = true">[+ Nuevo]</a>
                                </label>
                                <el-select v-model="form.sender_id" filterable clearable
                                    placeholder="Escriba el nombre o numero de documento del remitente" style="width:100%">
                                    <el-option v-for="option in all_persons" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.sender_id" class="form-control-feedback" v-text="errors.sender_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors.receiver_id}" class="form-group">
                                <label class="control-label">
                                    Destinatario<span class="text-danger"> *</span>
                                    <a href="#" class="ml-2 text-info font-weight-bold"
                                        @click.prevent="showDialogNewPerson = true">[+ Nuevo]</a>
                                </label>
                                <el-select v-model="form.receiver_id" filterable clearable
                                    placeholder="Escriba el nombre o numero de documento del destinatario" style="width:100%">
                                    <el-option v-for="option in all_persons" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.receiver_id" class="form-control-feedback" v-text="errors.receiver_id[0]"></small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4>Datos envío</h4>
                    <h6>Dirección partida</h6>
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="control-label">País<span class="text-danger"> *</span></label>
                                <el-select v-model="form.origin.country_id" filterable style="width:100%">
                                    <el-option v-for="option in countries" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Ubigeo<span class="text-danger"> *</span></label>
                                <el-cascader v-model="form.origin.location_id" :options="locations"
                                    filterable style="width:100%"></el-cascader>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors['origin.address']}" class="form-group">
                                <label class="control-label">Dirección<span class="text-danger"> *</span></label>
                                <el-input v-model="form.origin.address" :maxlength="100" placeholder="Dirección..."></el-input>
                                <small v-if="errors['origin.address']" class="form-control-feedback" v-text="errors['origin.address'][0]"></small>
                            </div>
                        </div>
                    </div>

                    <h6>Dirección llegada</h6>
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="control-label">País<span class="text-danger"> *</span></label>
                                <el-select v-model="form.delivery.country_id" filterable style="width:100%">
                                    <el-option v-for="option in countries" :key="option.id"
                                        :label="option.description" :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Ubigeo<span class="text-danger"> *</span></label>
                                <el-cascader v-model="form.delivery.location_id" :options="locations"
                                    filterable style="width:100%"></el-cascader>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div :class="{'has-danger': errors['delivery.address']}" class="form-group">
                                <label class="control-label">Dirección<span class="text-danger"> *</span></label>
                                <el-input v-model="form.delivery.address" :maxlength="100" placeholder="Dirección..."></el-input>
                                <small v-if="errors['delivery.address']" class="form-control-feedback" v-text="errors['delivery.address'][0]"></small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4>Datos transportista</h4>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Selección rápida de transportista</label>
                                <el-select v-model="selected_carrier" clearable style="width:100%"
                                    @change="changeCarrier">
                                    <el-option v-for="option in carriers" :key="option.id"
                                        :label="option.number + ' - ' + option.name" :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-12">&nbsp;</div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors['carrier.number']}" class="form-group">
                                <label class="control-label">RUC Transportista<span class="text-danger"> *</span></label>
                                <el-input v-model="form.carrier.number" :maxlength="11" placeholder="20xxxxxxxxx"></el-input>
                                <small v-if="errors['carrier.number']" class="form-control-feedback" v-text="errors['carrier.number'][0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors['carrier.name']}" class="form-group">
                                <label class="control-label">Razón Social Transportista<span class="text-danger"> *</span></label>
                                <el-input v-model="form.carrier.name" :maxlength="100" placeholder="Razón social..."></el-input>
                                <small v-if="errors['carrier.name']" class="form-control-feedback" v-text="errors['carrier.name'][0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors.license_plate}" class="form-group">
                                <label class="control-label">Número de placa del vehículo</label>
                                <el-input v-model="form.license_plate" :maxlength="8" placeholder="ABC-123..."></el-input>
                                <small v-if="errors.license_plate" class="form-control-feedback" v-text="errors.license_plate[0]"></small>
                            </div>
                        </div>
                    </div>

                    <h4>Datos conductor</h4>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Selección rápida de conductor</label>
                                <el-select v-model="selected_driver" clearable style="width:100%"
                                    @change="changeDriver">
                                    <el-option v-for="option in drivers" :key="option.id"
                                        :label="option.number + ' - ' + option.name" :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-12">&nbsp;</div>
                        <div class="col-lg-4">
                            <div :class="{'has-danger': errors['driver.number']}" class="form-group">
                                <label class="control-label">DNI Conductor</label>
                                <el-input v-model="form.driver.number" :maxlength="11" placeholder="DNI..."></el-input>
                                <small v-if="errors['driver.number']" class="form-control-feedback" v-text="errors['driver.number'][0]"></small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Nombre Conductor</label>
                                <el-input v-model="form.driver.name" :maxlength="100" placeholder="Nombre..."></el-input>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">Licencia del conductor</label>
                                <el-input v-model="form.driver.license"></el-input>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">N° placa semirremolque</label>
                                <el-input v-model="form.secondary_license_plates.semitrailer"></el-input>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="font-weight-bold">Unidad</th>
                                    <th class="font-weight-bold">Descripción</th>
                                    <th class="text-right font-weight-bold">Cantidad</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="form.items.length > 0">
                                <tr v-for="(row, index) in form.items" :key="index">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ row.unit_type_id }}</td>
                                    <td>{{ row.description }}</td>
                                    <td class="text-right">{{ row.quantity }}</td>
                                    <td class="text-right">
                                        <button class="btn waves-effect waves-light btn-xs btn-danger"
                                            type="button" @click.prevent="clickRemoveItem(index)">x</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-right hidden-sm-down" colspan="2">
                                        <label class="control-label">Producto</label>
                                    </td>
                                    <td class="hidden-sm-down" colspan="2">
                                        <div class="row">
                                            <div class="col-8">
                                                <div :class="{'has-danger': errors.items}" class="form-group">
                                                    <el-select v-model="current_item"
                                                        :loading="loading_search_item"
                                                        :remote-method="searchRemoteItems"
                                                        filterable remote ref="selectItem"
                                                        placeholder="Buscar producto..."
                                                        @change="onChangeItem">
                                                        <el-option v-for="option in items" :key="option.id"
                                                            :label="option.description" :value="option.id"></el-option>
                                                    </el-select>
                                                    <small v-if="errors.items" class="form-control-feedback" v-text="errors.items[0]"></small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <el-input-number v-model="quantity" :max="99999999"
                                                        :min="0.0001" :precision="4" :step="1"
                                                        placeholder="Cantidad"></el-input-number>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right hidden-sm-down">
                                        <el-button style="width:100%" type="primary" @click="addAItemInRow">Agregar</el-button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-lg-12">&nbsp;</div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button v-if="form.items.length > 0"
                        :loading="loading_submit" native-type="submit" type="primary">Generar</el-button>
                </div>
            </form>
        </div>

        <person-form :external="true"
                     :showDialog.sync="showDialogNewPerson"
                     type="customers"></person-form>
    </div>
</template>

<script>
import PersonForm from '../persons/form.vue';

export default {
    components: { PersonForm },
    data() {
        return {
            resource: 'carrier-guides',
            loading_submit: false,
            loading_search_sender: false,
            loading_search_receiver: false,
            loading_search_item: false,
            showDialogNewPerson: false,
            senders: [],
            receivers: [],
            all_persons: [],
            items: [],
            carriers: [],
            drivers: [],
            selected_carrier: null,
            selected_driver: null,
            current_item: null,
            quantity: 1,
            transferReasonTypes: [],
            transportModeTypes: [],
            unitTypes: [],
            countries: [],
            locations: [],
            series: [],
            errors: {},
            form: {
                series: null,
                date_of_issue: moment().format('YYYY-MM-DD'),
                date_of_shipping: moment().format('YYYY-MM-DD'),
                sender_id: null,
                receiver_id: null,
                carrier: { number: '', name: '' },
                driver: { number: '', name: '', license: '' },
                license_plate: '',
                secondary_license_plates: { semitrailer: null },
                transport_mode_type_id: null,
                transfer_reason_type_id: null,
                transfer_reason_description: '',
                unit_type_id: 'KGM',
                total_weight: 1,
                packages_number: 0,
                origin: { country_id: 'PE', location_id: [], address: '' },
                delivery: { country_id: 'PE', location_id: [], address: '' },
                observations: '',
                items: [],
            },
        };
    },
    mounted() {
        this.$http.get(`/${this.resource}/tables`).then(response => {
            this.transferReasonTypes = response.data.transfer_reason_types || [];
            this.transportModeTypes  = response.data.transport_mode_types  || [];
            this.unitTypes           = response.data.unit_types             || [];
            this.series              = response.data.series                 || [];
            this.carriers            = response.data.carriers               || [];
            this.drivers             = response.data.drivers                || [];
            this.countries           = response.data.countries              || [];
            this.locations           = response.data.locations              || [];
            this.all_persons         = response.data.customers              || [];
        });

        this.$eventHub.$on('reloadDataPersons', (customer_id) => {
            this.reloadDataPersons(customer_id);
        });
    },
    methods: {
        reloadDataPersons(customer_id) {
            this.$http.get(`/${this.resource}/tables`).then(response => {
                this.all_persons = response.data.customers || [];
                if (customer_id) {
                    this.form.sender_id = customer_id;
                }
            });
        },
        changeCarrier() {
            const c = this.carriers.find(x => x.id === this.selected_carrier);
            if (c) {
                this.form.carrier.number = c.number;
                this.form.carrier.name   = c.name;
            }
        },
        changeDriver() {
            const d = this.drivers.find(x => x.id === this.selected_driver);
            if (d) {
                this.form.driver.number  = d.number;
                this.form.driver.name    = d.name;
                this.form.driver.license = d.license;
            }
        },
        async searchRemoteItems(input) {
            if (!input || input.length < 2) return;
            this.loading_search_item = true;
            const { data } = await this.$http.get(`/${this.resource}/search-items`, { params: { search: input } });
            this.items = data;
            this.loading_search_item = false;
        },
        onChangeItem() {},
        addAItemInRow() {
            this.errors = {};
            if (!this.current_item) {
                this.$set(this.errors, 'items', ['Seleccione el producto']);
                return;
            }
            const item = this.items.find(i => i.id == this.current_item);
            if (!item) return;
            const exist = this.form.items.find(i => i.item_id == item.id);
            if (exist) {
                exist.quantity += this.quantity;
            } else {
                this.form.items.push({
                    item_id: item.id,
                    description: item.description,
                    unit_type_id: item.unit_type_id || 'NIU',
                    quantity: this.quantity,
                });
            }
            this.current_item = null;
            this.quantity = 1;
            this.$refs.selectItem.$el.getElementsByTagName('input')[0].focus();
        },
        clickRemoveItem(index) {
            this.form.items.splice(index, 1);
        },
        submit() {
            if (this.form.origin.location_id.length != 3 || this.form.delivery.location_id.length != 3) {
                return this.$message.error('El campo ubigeo es obligatorio');
            }
            this.loading_submit = true;
            this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                }).catch(error => {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response ? error.response.data.message : error.message);
                    }
                }).then(() => {
                    this.loading_submit = false;
                });
        },
        close() {
            location.href = '/carrier-guides';
        },
    },
};
</script>