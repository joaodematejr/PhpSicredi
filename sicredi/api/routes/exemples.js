var express = require('express')
var router = express.Router()
var xml = require('xml')

router.get('/', function (req, res, next) {
  const { name, age } = req.body
  var renderXml = [{ toys: [{ name: name }, { name: name }, { name: name }] }]
  res.type('application/xml')
  res.send(xml(renderXml, true))
})

module.exports = router
