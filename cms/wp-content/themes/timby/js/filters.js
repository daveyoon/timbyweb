angular.module('timby.filters', [])
.filter('searchVerifiedReportsFilter', function(){
  return function(reports, search){
    if( typeof(search) === 'undefined' ) return;

    var r = new RegExp(search, 'i');
    var result = [];

    if(search.length > 0){
      angular.forEach(reports, function(report, key){
        if( report.verified && r.test(report.post_title)){
          result.push(report);
        }
      });
    }
    return result;
  }
})
.filter('searchFilter', function(){
  return function(reports, search){
    if( search.length === 0) return reports;

    var r = new RegExp(search, 'i');
    var result = [];

    if(search.length > 0){
      angular.forEach(reports, function(report, key){
        if(r.test(report.post_title)){
          result.push(report);
        }
      });
    }

    return result;

  }
})
.filter('sectorFilter', function(){
  return function(reports, sectors){

    if( sectors.length === 0) return reports;

  
    if( sectors.length > 0 ){
      var result = [];
      var _sector_ids = sectors.map(grab_object_id);

      angular.forEach(reports, function(report, key){
        if( report.sectors.length > 0){
          // find single sector
          if( _sector_ids.length == 1){
            for (var i = report.sectors.length - 1; i >= 0; i--) {
              if( _sector_ids.indexOf(report.sectors[i].id) !== -1 ) 
                result.push(report); //push report
            }
          } else{
            // find multiple sectors
            var _report_sectors = report.sectors.map(grab_object_id);
            if( _sector_ids.sort().join() == _report_sectors.sort().join()){
              result.push(report);
            }   
          }
        }
      });      
    }
    // console.log('Total sectors selected '+_sector_ids.length);
    // console.log('Total results found '+result.length);

    return result;
  }
})
.filter('entityFilter', function(){
  return function(reports, entities){

    if( entities.length === 0) return reports;

    var result = [];

    if( entities.length > 0 ){
      var _entity_ids = entities.map(grab_object_id);

      angular.forEach(reports, function(report, key){
        // find single entity
        if( _entity_ids.length == 1){
          for (var i = report.entities.length - 1; i >= 0; i--) {
            if( _entity_ids.indexOf(report.entities[i].id) !== -1 ) 
              result.push(report); //push report
          }
        } else{
          // find multiple entities
          var _report_entities = report.entities.map(grab_object_id);
          if( _entity_ids.sort().join() == _report_entities.sort().join()){
            result.push(report);
          }   
        }
      });
    }

    return result;
  }
})
.filter('verifiedStatusFilter', function(){
  return function(reports, status){
    if( typeof(status) === 'undefined' ) return reports;

    var result = [];
  
    angular.forEach(reports, function(report, key){
      if( report.verified && status.indexOf('verified') !== -1 ){
        result.push(report);
      } 
      if( !report.verified && status.indexOf('unverified') !== -1){
        result.push(report);
      } 
    });
    return result;
  }
});

function grab_object_id(item){
  return item.id
}