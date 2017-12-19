var lng = '';
var lat = '';

// map(选择具体地址)
var map = new AMap.Map('container', {
    resizeEnable: true,
    zoom: 12,
    // center:[];
});
var geocoder;

//点击获取地址
AMap.plugin(['AMap.Geocoder', 'AMap.PlaceSearch', 'AMap.Autocomplete', 'AMap.Geolocation'], function () {
    var geocoder = new AMap.Geocoder({

    });
    var marker = new AMap.Marker({
        map: map,
        bubble: true
    })
    //设置深圳站定位
    var aa = {
        N: 114.118048,
        Q: 22.529559,
        lat: 22.529559,
        lng: 114.118048,
    }

    marker.setPosition(aa);
    
    map.on('click', function (e) {
        marker.setPosition(e.lnglat);
        geocoder.getAddress(e.lnglat, function (status, result) {
            if (status == 'complete') {
                alert(result.regeocode.formattedAddress)
            }
        })
    })
    geocoder.getLocation("广东省深圳市南山区桂庙路口116号", function (status, result) {
        if (status === 'complete' && result.info === 'OK') {
            //TODO:获得了有效经纬度，可以做一些展示工作
            console.log(result);

        } else {
            //获取经纬度失败
            console.log('获取经纬度失败');
        }
    });


    geolocation = new AMap.Geolocation({
        enableHighAccuracy: true,//是否使用高精度定位，默认:true
        timeout: 10000,          //超过10秒后停止定位，默认：无穷大
        maximumAge: 0,           //定位结果缓存0毫秒，默认：0
        convert: true,           //自动偏移坐标，偏移后的坐标为高德坐标，默认：true
        showButton: true,        //显示定位按钮，默认：true
        buttonPosition: 'RB',    //定位按钮停靠位置，默认：'LB'，左下角
        buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
        showMarker: true,        //定位成功后在定位到的位置显示点标记，默认：true
        showCircle: true,        //定位成功后用圆圈表示定位精度范围，默认：true
        panToLocation: true,     //定位成功后将定位到的位置作为地图中心点，默认：true
        zoomToAccuracy: true      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
    });
    geolocation.getCurrentPosition(function (status, result) {
        console.log(result);
    });


    var autoOptions = {
        city: "深圳市", //城市，默认全国
        input: "keyword"//使用联想输入的input的id
    };
    autocomplete = new AMap.Autocomplete(autoOptions);
    var placeSearch = new AMap.PlaceSearch({
        city: '',
        map: map
    })
    AMap.event.addListener(autocomplete, "select", function (e) {
        //TODO 针对选中的poi实现自己的功能
        placeSearch.setCity(e.poi.adcode);
        placeSearch.search(e.poi.name)
        console.log(e.poi)
        console.log(e.poi.location.lng)
        console.log(e.poi.location.lat)
        lng = e.poi.location.lng
        lat = e.poi.location.lat
    });
});