<template>
    <div class="page-container">
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
        />

        <widget
            :id="'meter-list'"
            :title="'Meters'"
            :paginator="meters.paginator"
            :search="true"
            :subscriber="subscriber"
            :route_name="'/meters'"
        >

            <div v-if="meters.list.length>0">
                <md-table md-card style="margin-left: 0">
                    <md-table-row>
                        <md-table-head>ID</md-table-head>
                        <md-table-head>
                            <font-awesome-icon icon="plus"/>
                            Serial Number
                        </md-table-head>
                        <md-table-head>
                            <font-awesome-icon icon="plus"/>
                            Tariff
                        </md-table-head>
                        <md-table-head>Manufacturer</md-table-head>
                        <md-table-head>Type</md-table-head>
                        <md-table-head>Last update</md-table-head>
                    </md-table-row>

                    <md-table-row
                        v-for="meter in meters.list"
                        :key="meter.id"
                        :class="meter.inUse===1 ? 'active': 'warning'"
                        style="cursor:pointer;"
                        @click="meterDetail( meter.serialNumber)"
                    >
                        <md-table-cell>{{ meter.id}}</md-table-cell>
                        <md-table-cell>{{ meter.serialNumber}}</md-table-cell>
                        <md-table-cell>{{meter.tariff}}</md-table-cell>
                        <md-table-cell>{{ meter.manufacturer.manufacturerName}}</md-table-cell>
                        <md-table-cell>
                            {{meter.type}}
                            <span
                                v-if="meter.online"
                                class="label label-success"
                                style="padding: 5px; font-size: 1.2rem"
                            >
              <i class="fa fa-globe"></i>
            </span>
                        </md-table-cell>
                        <md-table-cell>{{meter.lastUpdate}}</md-table-cell>
                    </md-table-row>
                </md-table>
            </div>
            <div v-else>
                <no-table-data :headers="headers" :tableName="tableName"/>
            </div>

        </widget>
    </div>
</template>

<script>
    import Widget from '../../shared/widget'
    import { Meters } from '../../classes/Meters'
    import { Manufacturers } from '../../classes/Manufacturer'
    import { EventBus } from '../../shared/eventbus'
    import NoTableData from '../../shared/NoTableData'

    export default {
        name: 'MeterList',
        components: { Widget, NoTableData },
        data () {
            return {
                meters: new Meters(),
                manufacturers: new Manufacturers(),
                subscriber: 'meterList',
                headers: ['ID', 'Serial Number', 'Tariff', 'Manufacturer', 'Type', 'Last update'],
                tableName: 'Meter'
            }
        },

        mounted () {
            EventBus.$emit('bread', this.bcd)
            EventBus.$on('pageLoaded', this.reloadList)
            EventBus.$on('searching', this.searching)
            EventBus.$on('end_searching', this.endSearching)
        },
        beforeDestroy () {
            EventBus.$off('pageLoaded', this.reloadList)
            EventBus.$off('searching', this.searching)
            EventBus.$off('end_searching', this.endSearching)
        },
        methods: {
            reloadList (subscriber, data) {
                if (subscriber !== this.subscriber) return
                this.meters.updateList(data)
            },
            confirmDelete (meter) {
                this.$swal({
                    type: 'question',
                    title: 'Delete Meter',
                    width: '25%',
                    confirmButtonText: 'Confirm',
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    focusCancel: true,
                    html:
                        '<div style="text-align: left; padding-left: 5rem" class="checkbox">' +
                        '  <label>' +
                        '    <input type="checkbox" name="confirmation" id="confirmation" >' +
                        '   I confirm to delete ' +
                        meter.serialNumber +
                        '  </label>' +
                        '</div>'
                }).then(result => {
                    let answer = document.getElementById('confirmation').checked
                    if ('value' in result) {
                        //delete customer
                        if (answer) {
                            this.deleteMeter(meter.id)
                        } else {
                            const Toast = this.$swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                onOpen: toast => {
                                    toast.addEventListener('mouseenter', this.$swal.stopTimer)
                                    toast.addEventListener('mouseleave', this.$swal.resumeTimer)
                                }
                            })

                            Toast.fire({
                                type: 'warning',
                                title: 'You have to confirm to delete the meter'
                            })
                        }
                    }
                })
            },
            deleteMeter (meterId) {
                axios.delete(resources.meters.delete + meterId).then(response => {
                    const Toast = this.$swal.mixin({
                        toast: true,
                        //position: 'center',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        onOpen: toast => {
                            //toast.addEventListener('mouseenter', this.$swal.stopTimer)
                            //toast.addEventListener('mouseleave', this.$swal.resumeTimer)
                        }
                    })

                    Toast.fire({
                        type: 'success',
                        title: 'Meter Deleted successfully'
                    }).then(x => {
                        location.reload()
                    })
                })
            },
            searching (searchTerm) {

                this.meters.search(searchTerm)
            },
            endSearching () {
                this.meters.showAll()
            },
            meterDetail (serialNumber) {
                this.$router.push({ path: '/meters/' + serialNumber })
            }
        }
    }
</script>

<style scoped>
</style>

