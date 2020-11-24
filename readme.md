# Next

This is an experiment to create something that's as elegant as NextJS, with some of the craeture comforts of the Laravel framework. There's lots still to do, but I thought it would be useful to get it out in the open while I add a few non-essential things to it.

## Roadmap

This list is not exhaustive or prioritised:

- [ ] Add Framework tests
- [ ] Add Framework documentation
- [ ] Refine cache and session dx
- [ ] Add more commands (migrate:fresh, make, serve)
- [x] Add middleware layer
- [x] Add session middleware
- [ ] Add rate limit middleware
- [ ] Add security middleware (cors, csp, csrf)
- [ ] Add more cache and session drivers
- [x] Support extension and request method negotiation
- [x] Store route params in request and make them accessible like headers or cookies
- [ ] Support route overrides in config
- [ ] Add phpx template layer
- [ ] Add sourcemap support for phpx
- [ ] Add phpx interactivity
- [ ] Add example tests in demo app
- [ ] Come up with proper name + logo

## Getting started

- Clone this repo
- Run `composer install`
- Clone the demo app alongside the this repo (same parent folder)
- Run `composer install` in the demo app
- Run `composer run serve` in the demo app
