-- Demo content (safe sample headlines + stories)
-- Import after `install.sql`

USE newsportal;

-- Clear existing demo news (optional)
-- DELETE FROM news;

INSERT INTO news
  (title, slug, summary, content, image_path, category_id, country_id, source_url, is_featured, published_at, status, author_user_id)
VALUES
  (
    'Demo: Nepal parliament session focuses on economic priorities',
    'nepal-parliament-session-focuses-on-economic-priorities',
    'Lawmakers discussed near-term economic priorities, budget timelines, and public service delivery.',
    '<p><strong>Kathmandu</strong> — Nepal’s Parliament held a session focused on economic priorities and near-term policy goals.</p><p>Members highlighted budget planning, job creation, and improving delivery of public services. Committee members also discussed timelines for upcoming hearings and the need for better coordination across ministries.</p><p>The session ended with a call for more structured debate and clear follow-up timelines.</p>',
    NULL,
    1,
    1,
    NULL,
    1,
    NOW(),
    'published',
    1
  ),
  (
    'Demo: Nepal local governments expand digital citizen services',
    'nepal-local-governments-expand-digital-citizen-services',
    'Municipal offices are adopting online forms and appointment systems to reduce queues and paperwork.',
    '<p>Several municipalities in Nepal have expanded digital services, including online applications and appointment booking.</p><ul><li>Online forms for common requests</li><li>Appointment scheduling for in-person verification</li><li>Status tracking for submitted applications</li></ul><p>Officials say the goal is to improve transparency and reduce processing time.</p>',
    NULL,
    4,
    1,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 3 HOUR),
    'published',
    1
  ),
  (
    'Demo: Nepal sports — domestic league teams prepare for busy season',
    'nepal-sports-domestic-league-teams-prepare-for-busy-season',
    'Teams announced training camps and friendly fixtures ahead of a packed schedule.',
    '<p>Clubs across Nepal have begun pre-season training with a focus on fitness and squad depth.</p><p>Coaches said a congested schedule makes rotation and injury prevention key priorities.</p>',
    NULL,
    3,
    1,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 8 HOUR),
    'published',
    1
  ),
  (
    'Demo: India markets watch inflation data and policy signals',
    'india-markets-watch-inflation-data-and-policy-signals',
    'Traders and analysts are closely tracking inflation prints and guidance from policymakers.',
    '<p>Investors in India are watching upcoming inflation releases and policy commentary.</p><p>Analysts say expectations around interest rates and fiscal planning could influence near-term market sentiment.</p>',
    NULL,
    2,
    2,
    NULL,
    1,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    'published',
    1
  ),
  (
    'Demo: United States tech firms invest in AI safety and testing',
    'united-states-tech-firms-invest-in-ai-safety-and-testing',
    'Companies are expanding evaluation teams and publishing more testing results.',
    '<p>Several U.S. technology firms announced expanded investments in AI evaluation, red-teaming, and safety testing.</p><p>Industry groups say stronger testing practices can reduce risk and improve reliability for users.</p>',
    NULL,
    4,
    3,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    'published',
    1
  ),
  (
    'Demo: United Kingdom businesses adapt to changing consumer spending',
    'united-kingdom-businesses-adapt-to-changing-consumer-spending',
    'Retailers are adjusting inventory and promotions as shopping patterns shift.',
    '<p>UK businesses are adjusting to changes in consumer spending by refining promotions and inventory planning.</p><p>Economists note that household budgets and energy costs remain important factors shaping demand.</p>',
    NULL,
    2,
    4,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 4 DAY),
    'published',
    1
  ),
  (
    'Demo: China manufacturing outlook steadies as firms optimize costs',
    'china-manufacturing-outlook-steadies-as-firms-optimize-costs',
    'Manufacturers report efficiency measures and a focus on stable supply chains.',
    '<p>Manufacturers in China report continued cost-optimization and a focus on supply-chain stability.</p><p>Industry sources say firms are prioritizing predictable deliveries and higher utilization rates.</p>',
    NULL,
    2,
    5,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 5 DAY),
    'published',
    1
  ),
  (
    'Demo: International tensions trigger urgent diplomatic talks',
    'international-tensions-trigger-urgent-diplomatic-talks',
    'Officials call for de-escalation as regional tensions rise in a developing situation.',
    '<p><strong>World</strong> — In a developing situation, multiple governments called for de-escalation and renewed diplomacy.</p><p>Analysts noted that information can change quickly and urged readers to follow official statements.</p><p><em>This is demo content for the AsuraNews project.</em></p>',
    NULL,
    1,
    3,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 12 HOUR),
    'published',
    1
  ),
  (
    'Demo: Energy prices fluctuate amid uncertainty',
    'energy-prices-fluctuate-amid-uncertainty',
    'Markets react to changing expectations, supply signals, and headline risk.',
    '<p>Energy prices moved as traders reacted to shifting expectations and headline risk.</p><p>Economists say demand forecasts, inventories, and policy signals all influence short-term volatility.</p><p><em>This is demo content for the AsuraNews project.</em></p>',
    NULL,
    2,
    4,
    NULL,
    0,
    DATE_SUB(NOW(), INTERVAL 18 HOUR),
    'published',
    1
  );

