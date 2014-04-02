angular.module('timby.filters', [])
.filter('searchFilter', function(){
  return function(reports, search){
    if( search.length === 0) return reports;

    var r = new RegExp(search, 'i');
    var result = [];

    // filter via search
    if( search.length === 0 ) 
      return reports;

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

    var result = [];
    var _sector_ids = sectors.map(function(sector){
      return sector.id
    });

    if( sectors.length > 0 ){
      angular.forEach(reports, function(report, key){
        if( report.sectors.length > 0){
          angular.forEach(report.sectors, function(sector, key){
            if( _sector_ids.indexOf(sector.id) != -1){
              result.push(report);
            }
          });
        }
      });      
    }

    return result;
  }
})
.filter('entityFilter', function(){
  return function(reports, entities){

    if( entities.length === 0) return reports;

    var result = [];
    var _entity_ids = entities.map(function(sector){
      return sector.id
    });

    if( entities.length > 0 ){
      angular.forEach(reports, function(report, key){
        if( report.entities.length > 0){
          angular.forEach(report.entities, function(sector, key){
            if( _entity_ids.indexOf(sector.id) != -1){
              result.push(report);
            }
          });
        }
      });      
    }

    return result;
  }
})