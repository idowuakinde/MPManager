<template>

    <widget
        title="MiniGrid Map"
        id="miniGrid-map">
        <Map
            :geoData="geoData"
            :center="center"
            :markerLocations="markerLocations"
            :constantLocations="constantLocations"
            :zoom="9"
            :constantMarkerUrl="miniGridIcon"
            :markerUrl="meterIcon"
            :edit="true"
            :markingInfos="markingInfos"
            :isMeter="true"
        />

    </widget>
</template>
<script>
    import Widget from '../../shared/widget'
    import { ClusterService } from '../../services/ClusterService'
    import { MappingService } from '../../services/MappingService'
    import Map from '../../shared/Map'
    import { MiniGridService } from '../../services/MiniGridService'
    import { MeterService } from '../../services/MeterService'
    import miniGridIcon from '../../../icons/miniGrid.png'
    import meterIcon from '../../../icons/meter.png'
    import { EventBus } from '../../shared/eventbus'

    export default {
        name: 'MiniGridMap',
        components: {
            Widget,
            Map,
        },
        data () {
            return {
                clusterService: new ClusterService(),
                mappingService: new MappingService(),
                miniGridService: new MiniGridService(),
                meterService: new MeterService(),
                meterIcon: meterIcon,
                miniGridIcon: miniGridIcon,
                miniGridLatLng: {
                    lat: null,
                    lon: null
                },
                meterLatLng: {
                    lat: null,
                    lon: null
                },
                markerLocations: [],
                constantLocations: [],
                markingInfos: [],
                loading: false,
                show: true,
                geoData: null,
                center: this.appConfig.mapStartingPoint,
                miniGrids: null,
                clusterLayer: null,
                clusterId: null,
                clusterGeo: {},
                meters: []
            }
        },
        computed: {},
        props: {
            miniGridId: {
                type: String,
                required: true
            }
        },
        mounted () {
            this.getMiniGrid(this.miniGridId)
            EventBus.$on('getEditedGeoDataItems', (editedItems) => {
                this.$swal({
                    title: 'Relocate Meter',
                    text: 'Are you sure you want to relocate the selected meters?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Relocate',
                    cancelButtonText: 'Dismiss'
                }).then((result) => {

                    if (result) {
                        editedItems.forEach(async (e) => {
                            try {
                                await this.meterService.updateMeter(e.id,e.lat,e.lng)
                                this.alertNotify('success', 'Meter is relocated successfully')
                            } catch (e) {
                                this.alertNotify('error', e.message)
                            }
                        })
                    }
                })

            })
        },
        methods: {
            async getGeoData (clusterId) {
                try {
                    this.clusterId = clusterId
                    let clusterGeoData = await this.clusterService.getClusterGeoLocation(clusterId)
                    this.center = [clusterGeoData.lat, clusterGeoData.lon]
                    this.geoData = this.mappingService.focusLocation(clusterGeoData)
                } catch (e) {
                    this.alertNotify('error', e.message)
                }
            },
            getMiniGrid: async function (miniGridId) {
                try {
                    this.markerLocations = []
                    this.constantLocations = []
                    this.markingInfos = []
                    let miniGridGeoData = await this.miniGridService.getMiniGridGeoData(miniGridId)
                    await this.getGeoData(miniGridGeoData.cluster_id)
                    let points = miniGridGeoData.location.points.split(',')
                    this.miniGridLatLng.lat = points[0]
                    this.miniGridLatLng.lon = points[1]
                    this.constantLocations.push([this.miniGridLatLng.lat, this.miniGridLatLng.lon])
                    await this.getMiniGridMeters(miniGridId)

                } catch (e) {
                    this.alertNotify('error', e.message)
                }
            },

            async getMiniGridMeters (miniGridId) {
                try {

                    this.meters = await this.meterService.getMeterGeos(miniGridId)
                    this.markerLocations = []
                    this.markingInfos = []
                    for (let i in this.meters) {
                        let points = this.meters[i].meter_parameter.address.geo.points.split(',')
                        this.meterLatLng.lat = points[0]
                        this.meterLatLng.lon = points[1]
                        let markingInfo = this.mappingService.createMarkinginformation(this.meters[i].id, null, this.meters[i].serial_number, points[0], points[1])
                        this.markingInfos.push(markingInfo)
                        this.markerLocations.push([this.meterLatLng.lat, this.meterLatLng.lon])
                    }
                } catch (e) {

                    this.alertNotify('error', e.message)
                }

            },
            alertNotify (type, message) {
                this.$notify({
                    group: 'notify',
                    type: type,
                    title: type + ' !',
                    text: message,
                    speed: 0
                })
            },
        },

    }
</script>

