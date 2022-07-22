locals {
  #we create a node pool of each type on each region
  node_pool_configs = {
    for pair in setproduct(var.regions, var.node_pool_configs) :
    "${pair[1].name}-${pair[0]}" => merge(pair[1], { "region" = pair[0] })
  }
}
