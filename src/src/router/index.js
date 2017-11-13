import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/components/Home'
import EventInfo from '@/components/EventInfo'
import EmployeeList from '@/components/EmployeeList'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home
    },
    {
      path: '/employee',
      name: 'employee',
      component: EmployeeList
    },
    {
      path: '/event/:key',
      name: 'eventInfo',
      component: EventInfo
    }
  ]
})
