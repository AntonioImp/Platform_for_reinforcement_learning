function drawChart(data, position, type) {
    var margin = {top: 20, right: 20, bottom: 30, left: 50},
    width = 600 - margin.left - margin.right,
    height = 300 - margin.top - margin.bottom;

    // append the svg object to the body of the page
    var svg = d3.select(position)
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var x = d3.scaleLinear()
        .rangeRound([0, width]);

    var y = d3.scaleLinear()
        .rangeRound([height, 0]);

    //create line
    var line = d3.line()
        .x(function(d) { return x(d.episode)})
        .y(function(d) { return y(d.value)})
        x.domain(d3.extent(data, function(d) { return d.episode }));
        y.domain(d3.extent(data, function(d) { return d.value }));

    // Add X axis --> it is a date format
    svg.append("g")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom().scale(x))
        .append("text")
        .attr("fill", "#000")
        .attr("x", 265)
        .attr("y", 28)
        .attr("dx", "0.8em")
        .attr("text-anchor", "end")
        .attr("font-weight", "bold")
        .attr("font-size", "13px")
        .text("Episode")
        .select(".domain");

    // Add Y axis
    svg.append("g")
        .call(d3.axisLeft().scale(y))
        .append("text")
        .attr("fill", "#000")
        .attr("transform", "rotate(-90)")
        .attr("x", -100)
        .attr("y", -48)
        .attr("dy", "0.71em")
        .attr("text-anchor", "end")
        .attr("font-weight", "bold")
        .attr("font-size", "13px")
        .text(type);

    // This allows to find the closest X index of the mouse:
    var bisect = d3.bisector(function(d) { return d.episode; }).left;

    // Create the circle that travels along the curve of chart
    var focus = svg.append('g')
        .append('circle')
            .style("fill", "none")
            .attr("stroke", "black")
            .attr('r', 8.5)
            .style("opacity", 0);

    // Create the text that travels along the curve of chart
    var focusText = svg.append('g')
        .append('text')
            .style("opacity", 0)
            .attr("text-anchor", "left")
            .attr("alignment-baseline", "middle")
            .attr("x", 10);

    // Create a rect on top of the svg area: this rectangle recovers mouse position
    svg.append('rect')
        .style("fill", "none")
        .style("pointer-events", "all")
        .attr('width', width)
        .attr('height', height)
        .on('mouseover', mouseover)
        .on('mousemove', mousemove)
        .on('mouseout', mouseout);

    svg.append("path")
        .datum(data)
        .attr("fill", "none")
        .attr("stroke", "steelblue")
        .attr("stroke-linejoin", "round")
        .attr("stroke-linecap", "round")
        .attr("stroke-width", 1.5)
        .attr("d", line);

    // What happens when the mouse move -> show the annotations at the right positions.
    function mouseover() {
        focus.style("opacity", 1)
        focusText.style("opacity",1)
    }

    function mousemove() {
        // recover coordinate we need
        var x0 = x.invert(d3.mouse(this)[0]);
        var i = bisect(data, x0, 1);
        selectedData = data[i];
        if(selectedData != null)
        {
            focus
                .attr("cx", x(selectedData.episode))
                .attr("cy", y(selectedData.value));
            focusText
                .html("Episode:" + selectedData.episode + "  -  " + type + ":" + selectedData.value)
                .attr("Episode", x(selectedData.episode)+15)
                .attr(type, y(selectedData.value));
        }
    }

    function mouseout() {
        focus.style("opacity", 0)
        focusText.style("opacity", 0)
    }
}




































//----------------- Note --------------------

//disegna linee orizzontali
//const makeYLines = () => d3.axisLeft()
//     .scale(y);
//inserisce linee orizzontali
// g.append('g')
//     .attr('class', 'grid')
//     .call(makeYLines()
//       .tickSize(-width, 0, 0)
//       .tickFormat(''));

// const api = 'https://api.coindesk.com/v1/bpi/historical/close.json?start=2017-12-31&end=2018-04-01';
// document.addEventListener("DOMContentLoaded", function(event) {
// fetch(api)
//     .then(function(response) { return response.json(); })
//     .then(function(data) {
//         var parsedData = parseData(data);
//         drawChart(parsedData);
//     })
//     .catch(function(err) { console.log(err); })
// });

// function parseData(data) {
//     var arr = [];
//     for (var i in data.bpi) {
//         arr.push({
//             date: new Date(i), //date
//             value: +data.bpi[i] //convert string to number
//         });
//     }
//     console.log(arr);
//     return arr;
// }
